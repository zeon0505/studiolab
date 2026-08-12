<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReturnDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'booking:remind-return';

    /**
     * The console command description.
     */
    protected $description = 'Kirim notifikasi WhatsApp ke peminjam pada hari deadline pengembalian barang.';

    public function handle(WhatsAppService $whatsApp): int
    {
        $today = Carbon::today();

        // Cari semua booking yang:
        // - Status disetujui (belum selesai/belum dikembalikan)
        // - Tanggal pengembalian = hari ini
        // - Tidak punya jam_mulai (artinya bukan sewa studio per jam, tapi peminjaman alat/peralatan multi-hari)
        $bookings = Booking::with('items')
            ->whereDate('tanggal_pengembalian', $today)
            ->whereIn('status', ['disetujui'])
            ->whereNull('jam_mulai')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('[Reminder] Tidak ada peminjaman yang jatuh tempo hari ini.');
            Log::info('[Reminder Return] Tidak ada booking deadline hari ini: ' . $today->toDateString());
            return Command::SUCCESS;
        }

        $this->info("[Reminder] Ditemukan {$bookings->count()} peminjaman jatuh tempo hari ini ({$today->toDateString()}).");

        foreach ($bookings as $booking) {
            if (empty($booking->no_wa)) {
                $this->warn("  ⚠ Booking #{$booking->id} — nomor WA tidak tersedia, skip.");
                continue;
            }

            // Kumpulkan nama semua item yang dipinjam
            $namaItems = $booking->items->pluck('nama')->implode(', ');
            if (empty($namaItems)) {
                $namaItems = 'Item Peminjaman';
            }

            $sent = $whatsApp->notifyDeadlinePengembalian([
                'booking_id'     => $booking->id,
                'no_wa'          => $booking->no_wa,
                'nama_peminjam'  => $booking->nama_peminjam,
                'nama_item'      => $namaItems,
                'tanggal_kembali'=> $today->format('d M Y'),
            ]);

            if ($sent) {
                $this->info("  ✅ Reminder terkirim ke {$booking->nama_peminjam} ({$booking->no_wa}) — Booking #{$booking->id}");
            } else {
                $this->error("  ❌ Gagal kirim reminder ke {$booking->nama_peminjam} ({$booking->no_wa}) — Booking #{$booking->id}");
            }
        }

        return Command::SUCCESS;
    }
}

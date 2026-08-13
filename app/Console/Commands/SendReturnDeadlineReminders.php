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
    protected $description = 'Kirim notifikasi WhatsApp ke peminjam pada H-1 dan H-0 deadline pengembalian barang.';

    public function handle(WhatsAppService $whatsApp): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // --- H-0: Hari Ini ---
        $bookingsToday = Booking::with('items')
            ->whereDate('tanggal_pengembalian', $today)
            ->whereIn('status', ['disetujui'])
            ->whereNull('jam_mulai')
            ->get();

        // --- H-1: Besok ---
        $bookingsTomorrow = Booking::with('items')
            ->whereDate('tanggal_pengembalian', $tomorrow)
            ->whereIn('status', ['disetujui'])
            ->whereNull('jam_mulai')
            ->get();

        $this->info("[Reminder] Memproses data reminder: Hari ini (" . $bookingsToday->count() . "), Besok (" . $bookingsTomorrow->count() . ")");

        // Proses Hari Ini
        foreach ($bookingsToday as $booking) {
            $this->sendReminder($booking, $whatsApp, 'HARI INI', $today->format('d M Y'));
        }

        // Proses Besok
        foreach ($bookingsTomorrow as $booking) {
            $this->sendReminder($booking, $whatsApp, 'BESOK', $tomorrow->format('d M Y'));
        }

        return Command::SUCCESS;
    }

    private function sendReminder(Booking $booking, WhatsAppService $whatsApp, string $waktu, string $tanggalFormatted)
    {
        if (empty($booking->no_wa)) {
            $this->warn("  ⚠ Booking #{$booking->id} — nomor WA tidak tersedia, skip.");
            return;
        }

        $namaItems = $booking->items->pluck('nama')->implode(', ') ?: 'Item Peminjaman';

        $sent = $whatsApp->notifyDeadlinePengembalian([
            'booking_id'     => $booking->id,
            'no_wa'          => $booking->no_wa,
            'nama_peminjam'  => $booking->nama_peminjam,
            'nama_item'      => $namaItems,
            'tanggal_kembali'=> $tanggalFormatted,
            'waktu_pengingat'=> $waktu,
        ]);

        if ($sent) {
            $this->info("  ✅ [{$waktu}] Reminder terkirim ke {$booking->nama_peminjam} ({$booking->no_wa}) — Booking #{$booking->id}");
        } else {
            $this->error("  ❌ [{$waktu}] Gagal kirim reminder ke {$booking->nama_peminjam} ({$booking->no_wa}) — Booking #{$booking->id}");
        }
    }
}

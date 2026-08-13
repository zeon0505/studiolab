<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiToken;
    protected string $apiUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->apiToken = config('services.fonnte.token', '');
    }

    /**
     * Kirim notifikasi WhatsApp ke Penanggung Jawab (PJ) saat ada booking baru.
     */
    public function notifyPj(array $bookingData): bool
    {
        $message = $this->buildMessageForPj($bookingData);
        $targetPhone = $bookingData['pj_no_wa'] ?? null;

        if (empty($targetPhone)) {
            Log::warning('[WhatsApp Bot] Nomor WA Penanggung Jawab tidak ditemukan. Pesan tidak terkirim.', [
                'booking_id' => $bookingData['booking_id'] ?? null,
            ]);
            return false;
        }

        return $this->send($targetPhone, $message, $bookingData['booking_id'] ?? null, 'PJ');
    }

    /**
     * Kirim konfirmasi WhatsApp ke peminjam saat booking diajukan.
     */
    public function notifyPeminjam(array $bookingData): bool
    {
        $message = $this->buildMessageForPeminjam($bookingData);
        $targetPhone = $bookingData['no_wa'] ?? null;

        if (empty($targetPhone)) {
            Log::warning('[WhatsApp Bot] Nomor WA Peminjam tidak ditemukan. Konfirmasi tidak terkirim.', [
                'booking_id' => $bookingData['booking_id'] ?? null,
            ]);
            return false;
        }

        return $this->send($targetPhone, $message, $bookingData['booking_id'] ?? null, 'Peminjam');
    }

    /**
     * Kirim notifikasi pembaruan status ke peminjam (disetujui / ditolak).
     */
    public function notifyStatusUpdate(array $data): bool
    {
        $message = $this->buildMessageStatusUpdate($data);
        $targetPhone = $data['no_wa'] ?? null;

        if (empty($targetPhone)) {
            Log::warning('[WhatsApp Bot] Nomor WA Peminjam tidak ditemukan untuk notif status update.', [
                'booking_id' => $data['booking_id'] ?? null,
            ]);
            return false;
        }

        return $this->send($targetPhone, $message, $data['booking_id'] ?? null, 'StatusUpdate');
    }

    /**
     * Kirim pesan WhatsApp via Fonnte API atau log simulasi.
     */
    protected function send(string $targetPhone, string $message, $bookingId = null, string $context = ''): bool
    {
        Log::info("[WhatsApp Bot][{$context}] Mencoba kirim notifikasi", [
            'target_phone' => $targetPhone,
            'booking_id'   => $bookingId,
            'message'      => $message,
        ]);

        if (!empty($this->apiToken)) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => $this->apiToken,
                ])->post($this->apiUrl, [
                    'target'      => $this->formatPhoneNumber($targetPhone),
                    'message'     => $message,
                    'countryCode' => '62',
                ]);

                if ($response->successful()) {
                    Log::info("[WhatsApp Bot][{$context}] Notifikasi berhasil dikirim ke: {$targetPhone}");
                    return true;
                } else {
                    Log::error("[WhatsApp Bot][{$context}] Gagal kirim ke Fonnte API", [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                Log::error("[WhatsApp Bot][{$context}] Exception saat kirim WA: " . $e->getMessage());
                return false;
            }
        }

        Log::info("[WhatsApp Bot][{$context}] SIMULASI — Token Fonnte belum dikonfigurasi. Pesan sudah dicatat di log.");
        return true;
    }

    /**
     * Format nomor telepon ke format internasional Indonesia.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Template pesan ke PJ: notifikasi booking baru masuk.
     */
    protected function buildMessageForPj(array $data): string
    {
        $tipe = $data['tipe_item'] === 'ruangan' ? 'Ruangan/Studio' : 'Peralatan';
        $jamInfo = '';

        if (!empty($data['jam_mulai']) && !empty($data['jam_selesai'])) {
            $jamInfo = "\n⏱ *Jam Sewa*       : {$data['jam_mulai']} - {$data['jam_selesai']} WIB";
        } else {
            $tanggalKembali = $data['tanggal_pengembalian'] ?? '-';
            $jamInfo = "\n📅 *Tgl Kembali*   : {$tanggalKembali}";
        }

        $lines = [
            "🔔 *[BOOKING BARU — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Assalamu'alaikum *{$data['pj_name']}*,",
            "",
            "Ada permohonan peminjaman baru yang perlu Anda tinjau dan setujui.",
            "",
            "📋 *DETAIL PEMINJAMAN*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "👤 *Nama Peminjam*  : {$data['nama_peminjam']}",
            "🏫 *Instansi/Prodi* : {$data['instansi_peminjam']}",
            "📱 *No. WhatsApp*   : {$data['no_wa']}",
            "",
            "🏷 *Item Dipinjam*  : {$data['nama_item']}",
            "📂 *Jenis*          : {$tipe} — " . ucfirst($data['kategori_item']),
            "📅 *Tgl Pinjam*    : {$data['tanggal_peminjaman']}" . $jamInfo,
            "",
            "📝 *Alasan/Catatan*:",
            ($data['catatan'] ?? '-'),
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "✅ Segera tinjau & setujui melalui dashboard admin:",
            config('app.url') . "/admin/dashboard",
            "",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }

    /**
     * Template konfirmasi ke peminjam: booking berhasil dikirim, menunggu persetujuan.
     */
    protected function buildMessageForPeminjam(array $data): string
    {
        $tipe = $data['tipe_item'] === 'ruangan' ? 'Ruangan/Studio' : 'Peralatan';
        $jamInfo = '';

        if (!empty($data['jam_mulai']) && !empty($data['jam_selesai'])) {
            $jamInfo = "\n⏱ *Jam Sewa*       : {$data['jam_mulai']} - {$data['jam_selesai']} WIB";
        } else {
            $tanggalKembali = $data['tanggal_pengembalian'] ?? '-';
            $jamInfo = "\n📅 *Tgl Kembali*   : {$tanggalKembali}";
        }

        $lines = [
            "✅ *[PERMOHONAN DITERIMA — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Halo *{$data['nama_peminjam']}*,",
            "",
            "Permohonan peminjaman Anda telah berhasil dikirim dan sedang menunggu persetujuan dari Penanggung Jawab.",
            "",
            "📋 *RINGKASAN PERMOHONAN*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "🏷 *Item Dipinjam*  : {$data['nama_item']}",
            "📂 *Jenis*          : {$tipe} — " . ucfirst($data['kategori_item']),
            "📅 *Tgl Pinjam*    : {$data['tanggal_peminjaman']}" . $jamInfo,
            "",
            "🟡 *Status*         : MENUNGGU PERSETUJUAN",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Pantau status permohonan Anda di:",
            config('app.url') . "/dashboard",
            "",
            "Kami akan menginformasikan hasilnya melalui WhatsApp ini.",
            "",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }

    /**
     * Template notifikasi status update ke peminjam (disetujui / ditolak).
     */
    protected function buildMessageStatusUpdate(array $data): string
    {
        $status = $data['status'];

        if ($status === 'disetujui') {
            $statusEmoji = "✅";
            $statusLabel = "DISETUJUI";
            $bodyText = "Selamat! Permohonan peminjaman Anda telah *disetujui* oleh Penanggung Jawab UPT. Silakan datang tepat waktu sesuai jadwal.";
        } elseif ($status === 'selesai') {
            $statusEmoji = "🏁";
            $statusLabel = "SELESAI";
            $bodyText = "Terima kasih! Peminjaman Anda telah ditandai sebagai *selesai*. Jangan lupa untuk mengembalikan item dalam kondisi baik.";
        } else {
            $statusEmoji = "❌";
            $statusLabel = "DITOLAK";
            $bodyText = "Mohon maaf, permohonan peminjaman Anda *tidak dapat disetujui* oleh Penanggung Jawab UPT."
                . (isset($data['alasan']) && !empty($data['alasan']) ? "\n\n📝 *Alasan*: {$data['alasan']}" : "");
        }

        $jamInfo = '';
        if (!empty($data['jam_mulai']) && !empty($data['jam_selesai'])) {
            $jamInfo = "\n⏱ *Jam Sewa*       : {$data['jam_mulai']} - {$data['jam_selesai']} WIB";
        } elseif (!empty($data['tanggal_pengembalian'])) {
            $jamInfo = "\n📅 *Tgl Kembali*   : {$data['tanggal_pengembalian']}";
        }

        $lines = [
            "{$statusEmoji} *[UPDATE STATUS — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Halo *{$data['nama_peminjam']}*,",
            "",
            $bodyText,
            "",
            "📋 *DETAIL PEMINJAMAN*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "🏷 *Item*          : {$data['nama_item']}",
            "📅 *Tgl Pinjam*   : {$data['tanggal_peminjaman']}" . $jamInfo,
            "",
            "{$statusEmoji} *Status* : {$statusLabel}",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Lihat detail permohonan di:",
            config('app.url') . "/dashboard",
            "",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }

    /**
     * Kirim notifikasi ke Admin bahwa user membatalkan bookingnya.
     */
    public function notifyBatalBooking(array $data): void
    {
        $message = $this->buildMessageBatalBooking($data);

        if (!empty($data['admin_no_wa'])) {
            $this->send($data['admin_no_wa'], $message, $data['booking_id'] ?? null, 'BatalBooking-Admin');
        }
    }

    protected function buildMessageBatalBooking(array $data): string
    {
        $lines = [
            "🚫 *[PEMBATALAN PEMINJAMAN — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Peminjam telah membatalkan permohonannya:",
            "",
            "👤 *Peminjam*     : {$data['nama_peminjam']}",
            "📱 *No. WA*       : {$data['no_wa_peminjam']}",
            "",
            "🏷 *Item*         : {$data['nama_item']}",
            "📅 *Tgl Pinjam*  : {$data['tanggal_peminjaman']}",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Booking telah dibatalkan oleh peminjam secara mandiri.",
            "",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }

    /**
     * Kirim notifikasi ke PJ dan Admin bahwa peminjam mengubah tanggal pengembalian.
     */
    public function notifyUbahTanggal(array $data): void
    {
        $message = $this->buildMessageUbahTanggal($data);

        // Kirim ke PJ yang bertugas (jika ada no WA)
        if (!empty($data['pj_no_wa'])) {
            $this->send($data['pj_no_wa'], $message, $data['booking_id'] ?? null, 'UbahTanggal-PJ');
        }

        // Kirim ke Admin utama
        if (!empty($data['admin_no_wa'])) {
            $this->send($data['admin_no_wa'], $message, $data['booking_id'] ?? null, 'UbahTanggal-Admin');
        }
    }

    /**
     * Template pesan notifikasi perubahan tanggal pengembalian.
     */
    protected function buildMessageUbahTanggal(array $data): string
    {
        $lines = [
            "📅 *[PERUBAHAN TANGGAL PENGEMBALIAN — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Informasi penting mengenai permohonan peminjaman yang sudah disetujui:",
            "",
            "👤 *Peminjam*        : {$data['nama_peminjam']}",
            "📱 *No. WhatsApp*    : {$data['no_wa_peminjam']}",
            "",
            "🏷 *Item Dipinjam*   : {$data['nama_item']}",
            "📅 *Tgl Pinjam*     : {$data['tanggal_peminjaman']}",
            "",
            "🔄 *Perubahan Tanggal Pengembalian:*",
            "   Lama : {$data['tanggal_lama']}",
            "   Baru : *{$data['tanggal_baru']}*",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Harap catat perubahan ini dan pastikan item dikembalikan sesuai tanggal baru.",
            "",
            "🔗 Lihat detail di dashboard admin:",
            config('app.url') . "/admin/dashboard",
            "",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }
    /**
     * Kirim notifikasi deadline pengembalian ke peminjam.
     */
    public function notifyDeadlinePengembalian(array $data): bool
    {
        $message = $this->buildMessageDeadlinePengembalian($data);
        $targetPhone = $data['no_wa'] ?? null;

        if (empty($targetPhone)) {
            Log::warning('[WhatsApp Bot] Nomor WA Peminjam tidak ditemukan untuk notif deadline pengembalian.', [
                'booking_id' => $data['booking_id'] ?? null,
            ]);
            return false;
        }

        return $this->send($targetPhone, $message, $data['booking_id'] ?? null, 'DeadlinePengembalian');
    }

    protected function buildMessageDeadlinePengembalian(array $data): string
    {
        $waktu = $data['waktu_pengingat'] ?? 'HARI INI';
        $lines = [
            "⏰ *[PENGINGAT PENGEMBALIAN — UPT STAIMAS]*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "Halo *{$data['nama_peminjam']}*,",
            "",
            "Ini adalah pengingat otomatis dari sistem UPT Studio & Lab STAIMAS Wonogiri.",
            "",
            "🔴 *{$waktu} adalah batas akhir pengembalian* untuk peminjaman berikut:",
            "",
            "📋 *DETAIL PEMINJAMAN*",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "🏷 *Barang Dipinjam* : {$data['nama_item']}",
            "📅 *Deadline Kembali*: {$data['tanggal_kembali']}",
            "",
            "⚠️ Mohon segera kembalikan barang/peralatan dalam kondisi baik ke petugas UPT sebelum jam operasional berakhir.",
            "",
            "Terima kasih atas kerja samanya! 🙏",
            "",
            "━━━━━━━━━━━━━━━━━━━━━━━━━━",
            "_Bot Portal UPT Studio & Lab STAIMAS Wonogiri_",
        ];

        return implode("\n", $lines);
    }
}

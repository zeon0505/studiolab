<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Jika admin/staff mengakses dashboard user biasa, alihkan ke dashboard admin
        if ($user->is_staff || in_array($user->email, ['admin@staimas.com', 'yoga@staimas.com'])) {
            return redirect()->route('admin.dashboard');
        }

        $query = Booking::with('items', 'penanggungJawab')
            ->where('user_id', $user->id);

        // Filter by status jika ada
        $filter = $request->query('filter');
        if ($filter && in_array($filter, ['pending', 'disetujui', 'ditolak', 'selesai', 'dibatalkan'])) {
            $query->where('status', $filter);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('user.dashboard', compact('bookings'));
    }

    /**
     * User mengedit tanggal pengembalian — hanya boleh selama status pending atau disetujui.
     */
    public function updateTanggal(Request $request, Booking $booking)
    {
        // Pastikan booking milik user yang login
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Bisa diedit selama masih pending atau disetujui
        if (!in_array($booking->status, ['pending', 'disetujui'])) {
            return back()->with('error', 'Tanggal pengembalian tidak dapat diubah karena peminjaman sudah selesai atau ditolak.');
        }

        $request->validate([
            'tanggal_pengembalian' => 'required|date|after_or_equal:' . $booking->tanggal_peminjaman->format('Y-m-d'),
        ], [
            'tanggal_pengembalian.required' => 'Tanggal pengembalian wajib diisi.',
            'tanggal_pengembalian.after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
        ]);

        $tanggalLama = $booking->tanggal_pengembalian ? $booking->tanggal_pengembalian->format('d M Y') : '-';
        $tanggalBaru = \Carbon\Carbon::parse($request->tanggal_pengembalian)->format('d M Y');

        $booking->update([
            'tanggal_pengembalian' => $request->tanggal_pengembalian,
        ]);

        // Catat audit log
        BookingLog::create([
            'booking_id'  => $booking->id,
            'user_id'     => Auth::id(),
            'from_status' => $booking->status,
            'to_status'   => $booking->status,
            'keterangan'  => "Peminjam mengubah tanggal pengembalian dari {$tanggalLama} menjadi {$tanggalBaru}.",
        ]);

        // Cari nomor WhatsApp PJ (Penanggung Jawab)
        $pjNoWa = null;
        if ($booking->penanggungJawab) {
            $pjNoWa = $booking->penanggungJawab->no_wa;
        } else {
            // Cari berdasarkan jadwal PJ harian di hari peminjaman tersebut
            $daysMap = [
                'Sunday'    => 'minggu',
                'Monday'    => 'senin',
                'Tuesday'   => 'selasa',
                'Wednesday' => 'rabu',
                'Thursday'  => 'kamis',
                'Friday'    => 'jumat',
                'Saturday'  => 'sabtu',
            ];
            $dayName = \Carbon\Carbon::parse($booking->tanggal_peminjaman)->format('l');
            $dayIndo = $daysMap[$dayName] ?? 'senin';
            $dailyAssignment = \App\Models\DailyAssignment::with('user')->where('hari', $dayIndo)->first();
            if ($dailyAssignment && $dailyAssignment->user) {
                $pjNoWa = $dailyAssignment->user->no_wa;
            }
        }

        // Cari nomor WhatsApp Admin utama
        $adminUser = \App\Models\User::where('email', 'admin@staimas.com')->first() 
            ?: \App\Models\User::where('email', 'yoga@staimas.com')->first();
        $adminNoWa = $adminUser ? $adminUser->no_wa : null;

        // Kirim notifikasi bot WhatsApp
        $booking->load('items');
        $namaItem = $booking->items->pluck('nama')->implode(', ');
        if (empty($namaItem)) {
            $namaItem = $booking->item ? $booking->item->nama : 'Item Peminjaman';
        }

        $whatsApp = app(\App\Services\WhatsAppService::class);
        $whatsApp->notifyUbahTanggal([
            'booking_id'        => $booking->id,
            'nama_peminjam'     => $booking->nama_peminjam,
            'no_wa_peminjam'    => $booking->no_wa,
            'nama_item'         => $namaItem,
            'tanggal_peminjaman'=> $booking->tanggal_peminjaman->format('d M Y'),
            'tanggal_lama'      => $tanggalLama,
            'tanggal_baru'      => $tanggalBaru,
            'pj_no_wa'          => $pjNoWa,
            'admin_no_wa'       => $adminNoWa,
        ]);

        return back()->with('success', 'Tanggal pengembalian berhasil diperbarui.');
    }
    /**
     * User membatalkan peminjaman — hanya boleh saat masih pending.
     */
    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Peminjaman hanya bisa dibatalkan selama status masih menunggu review.');
        }

        $booking->update(['status' => 'dibatalkan']);

        // Catat audit log
        BookingLog::create([
            'booking_id'  => $booking->id,
            'user_id'     => Auth::id(),
            'from_status' => 'pending',
            'to_status'   => 'dibatalkan',
            'keterangan'  => 'Dibatalkan oleh peminjam.',
        ]);

        // Notifikasi WA ke Admin
        $adminUser = \App\Models\User::where('email', 'admin@staimas.com')->first()
            ?: \App\Models\User::where('email', 'yoga@staimas.com')->first();
        $adminNoWa = $adminUser?->no_wa;

        $booking->load('items');
        $namaItem = $booking->items->pluck('nama')->implode(', ') ?: 'Item Peminjaman';

        app(\App\Services\WhatsAppService::class)->notifyBatalBooking([
            'booking_id'         => $booking->id,
            'nama_peminjam'      => $booking->nama_peminjam,
            'no_wa_peminjam'     => $booking->no_wa,
            'nama_item'          => $namaItem,
            'tanggal_peminjaman' => $booking->tanggal_peminjaman->format('d M Y'),
            'admin_no_wa'        => $adminNoWa,
        ]);

        return back()->with('success', 'Peminjaman berhasil dibatalkan.');
    }

    /**
     * User mengunggah foto bukti pengembalian barang.
     */
    public function uploadBuktiKembali(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if ($booking->status !== 'disetujui') {
            return back()->with('error', 'Unggah bukti pengembalian hanya dapat dilakukan untuk peminjaman yang aktif/disetujui.');
        }

        $request->validate([
            'foto_pengembalian' => 'required|image|max:2048',
        ], [
            'foto_pengembalian.required' => 'Foto bukti pengembalian wajib diunggah.',
            'foto_pengembalian.image' => 'File harus berupa gambar (JPG, PNG, JPEG).',
            'foto_pengembalian.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        if ($request->hasFile('foto_pengembalian')) {
            // Hapus foto lama jika ada
            if ($booking->foto_pengembalian) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($booking->foto_pengembalian);
            }

            $path = $request->file('foto_pengembalian')->store('bukti_pengembalian', 'public');
            $booking->update([
                'foto_pengembalian' => $path
            ]);

            // Catat log
            BookingLog::create([
                'booking_id'  => $booking->id,
                'user_id'     => Auth::id(),
                'from_status' => 'disetujui',
                'to_status'   => 'disetujui',
                'keterangan'  => 'Peminjam mengunggah foto bukti pengembalian.',
            ]);

            return back()->with('success', 'Foto bukti pengembalian berhasil diunggah. Menunggu konfirmasi pengembalian oleh petugas.');
        }

        return back()->with('error', 'Gagal mengunggah gambar.');
    }
}


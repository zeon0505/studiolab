<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Booking;
use App\Models\User;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;


class AdminController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang dimasukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
        // --- AUTO REMINDER: Cek deadline pengembalian hari ini ---
        $this->sendReturnDeadlineReminders();

        $query = Booking::with('items', 'penanggungJawab');

        // Filter Tipe (Peralatan vs Ruangan)
        if ($request->filled('tipe')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        // Filter Periode (Hari, Minggu, Bulan)
        if ($request->filled('periode')) {
            if ($request->periode === 'hari') {
                $query->whereDate('tanggal_peminjaman', Carbon::today());
            } elseif ($request->periode === 'minggu') {
                $query->whereBetween('tanggal_peminjaman', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($request->periode === 'bulan') {
                $query->whereMonth('tanggal_peminjaman', Carbon::now()->month)
                      ->whereYear('tanggal_peminjaman', Carbon::now()->year);
            }
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();
        
        $totalPending = Booking::where('status', 'pending')->count();
        $totalActive = Booking::where('status', 'disetujui')->count();

        // --- DATA CHART 1: Tren 7 Hari Terakhir ---
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');
            $chartData[] = Booking::whereDate('tanggal_peminjaman', $date)->count();
        }

        // --- DATA CHART 2: Rasio Ruangan vs Peralatan ---
        $countRuangan = Booking::whereHas('items', fn($q) => $q->where('tipe', 'ruangan'))->count();
        $countPeralatan = Booking::whereHas('items', fn($q) => $q->where('tipe', 'peralatan'))->count();

        return view('admin.dashboard', compact(
            'bookings', 
            'totalPending', 
            'totalActive',
            'chartLabels',
            'chartData',
            'countRuangan',
            'countPeralatan'
        ));
    }

    public function history(Request $request)
    {
        $query = Booking::with('items', 'penanggungJawab')
            ->whereIn('status', ['selesai', 'ditolak']);

        // Filter Pencarian Nama / Instansi / Nama Item
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_peminjam', 'like', '%' . $search . '%')
                  ->orWhere('instansi_peminjam', 'like', '%' . $search . '%')
                  ->orWhereHas('items', function($qi) use ($search) {
                      $qi->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter Tipe (Ruangan vs Peralatan)
        if ($request->filled('tipe')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('admin.history', compact('bookings'));
    }

    public function exportPdf(Request $request)
    {
        $query = Booking::with('items', 'penanggungJawab');

        // Filter Tipe
        if ($request->filled('tipe')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        // Filter Periode
        $filterPeriode = 'Semua Periode';
        if ($request->filled('periode')) {
            if ($request->periode === 'hari') {
                $query->whereDate('tanggal_peminjaman', Carbon::today());
                $filterPeriode = 'Hari Ini (' . Carbon::today()->translatedFormat('d F Y') . ')';
            } elseif ($request->periode === 'minggu') {
                $query->whereBetween('tanggal_peminjaman', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $filterPeriode = 'Minggu Ini (' . Carbon::now()->startOfWeek()->translatedFormat('d M') . ' - ' . Carbon::now()->endOfWeek()->translatedFormat('d M Y') . ')';
            } elseif ($request->periode === 'bulan') {
                $query->whereMonth('tanggal_peminjaman', Carbon::now()->month)
                      ->whereYear('tanggal_peminjaman', Carbon::now()->year);
                $filterPeriode = 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')';
            }
        }

        $bookings = $query->latest()->get();
        $filterTipe = $request->tipe;

        $pdf = Pdf::loadView('admin.pdf_report', compact('bookings', 'filterPeriode', 'filterTipe'));
        
        return $pdf->download('laporan-peminjaman-' . ($request->periode ?: 'semua') . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'no_wa' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_wa' => $request->no_wa,
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Staff / PJ baru berhasil ditambahkan.');
    }

    public function bookingShow(Booking $booking)
    {
        $booking->load('items', 'penanggungJawab');
        return view('admin.booking_detail', compact('booking'));
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak,selesai',
            'catatan' => 'nullable|string',
        ]);

        $booking->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        // If returned / selesai or rejected, change item status/stock back
        if (in_array($request->status, ['selesai', 'ditolak'])) {
            foreach ($booking->items as $item) {
                if ($item->tipe === 'ruangan') {
                    $item->update(['status' => 'tersedia']);
                } else {
                    $item->increment('stok', $item->pivot->jumlah);
                }
            }
        }

        // Kirim notifikasi status update ke peminjam via WhatsApp
        $booking->load('items');
        $namaItem = $booking->items->pluck('nama')->implode(', ');
        $whatsapp = app(WhatsAppService::class);
        $whatsapp->notifyStatusUpdate([
            'booking_id'         => $booking->id,
            'nama_peminjam'      => $booking->nama_peminjam,
            'no_wa'              => $booking->no_wa,
            'nama_item'          => $namaItem,
            'tanggal_peminjaman' => Carbon::parse($booking->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => $booking->tanggal_pengembalian
                ? Carbon::parse($booking->tanggal_pengembalian)->translatedFormat('d F Y')
                : null,
            'jam_mulai'          => $booking->jam_mulai ? substr($booking->jam_mulai, 0, 5) : null,
            'jam_selesai'        => $booking->jam_selesai ? substr($booking->jam_selesai, 0, 5) : null,
            'status'             => $request->status,
            'alasan'             => $request->catatan,
        ]);

        return back()->with('success', 'Status permohonan peminjaman berhasil diperbarui.');
    }

    public function itemsIndex(Request $request)
    {
        $tipe = $request->query('tipe', 'ruangan'); // default ke ruangan jika tidak dispesifikasikan
        $items = Item::where('tipe', $tipe)->latest()->get();
        return view('admin.items', compact('items', 'tipe'));
    }

    public function itemsStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:studio,laboratorium',
            'tipe' => 'required|in:peralatan,ruangan',
            'deskripsi' => 'nullable|string',
            'stok' => 'required|integer|min:1',
            'kapasitas_kursi' => 'nullable|integer|min:0',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('nama', 'kategori', 'tipe', 'deskripsi', 'stok', 'kapasitas_kursi');

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('items', 'public');
        }

        Item::create($data);
        return back()->with('success', 'Item inventaris baru berhasil ditambahkan.');
    }

    public function itemsUpdate(Request $request, Item $item)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:studio,laboratorium',
            'tipe' => 'required|in:peralatan,ruangan',
            'deskripsi' => 'nullable|string',
            'stok' => 'required|integer|min:1',
            'kapasitas_kursi' => 'nullable|integer|min:0',
            'status' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('nama', 'kategori', 'tipe', 'deskripsi', 'stok', 'kapasitas_kursi', 'status');

        if ($request->hasFile('gambar')) {
            if ($item->gambar) Storage::disk('public')->delete($item->gambar);
            $data['gambar'] = $request->file('gambar')->store('items', 'public');
        }

        $item->update($data);
        return back()->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function itemsDestroy(Item $item)
    {
        if ($item->gambar) Storage::disk('public')->delete($item->gambar);
        $item->delete();
        return back()->with('success', 'Item inventaris berhasil dihapus.');
    }

    public function assignmentsIndex()
    {
        $users = \App\Models\User::all();
        $assignments = \App\Models\DailyAssignment::with('user')->get()->keyBy('hari');
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        return view('admin.assignments', compact('users', 'assignments', 'days'));
    }

    public function assignmentsStore(Request $request)
    {
        $request->validate([
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'user_id' => 'required|exists:users,id',
        ]);

        \App\Models\DailyAssignment::updateOrCreate(
            ['hari' => $request->hari],
            ['user_id' => $request->user_id]
        );

        return back()->with('success', 'Penanggung Jawab (PJ) Harian berhasil diatur.');
    }

    public function updateUserWa(Request $request, \App\Models\User $user)
    {
        $request->validate([
            'no_wa' => 'nullable|string|max:20',
        ]);

        $user->update(['no_wa' => $request->no_wa]);

        return back()->with('success', 'Nomor WA ' . $user->name . ' berhasil diperbarui.');
    }

    public function assignmentsDestroy(\App\Models\DailyAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Penugasan PJ harian berhasil dihapus.');
    }

    /**
     * Cek booking yang deadline pengembaliannya hari ini dan kirim WA reminder otomatis.
     * Dipanggil setiap kali admin membuka dashboard — flag reminder_sent mencegah spam.
     */
    private function sendReturnDeadlineReminders(): void
    {
        $today = Carbon::today();

        $bookingsDeadline = Booking::with('items')
            ->whereDate('tanggal_pengembalian', $today)
            ->whereIn('status', ['disetujui'])
            ->whereNull('jam_mulai')
            ->where('reminder_sent', false)
            ->get();

        if ($bookingsDeadline->isEmpty()) {
            return;
        }

        $whatsApp = app(WhatsAppService::class);

        foreach ($bookingsDeadline as $booking) {
            if (empty($booking->no_wa)) {
                $booking->update(['reminder_sent' => true]);
                continue;
            }

            $namaItems = $booking->items->pluck('nama')->implode(', ');
            if (empty($namaItems)) {
                $namaItems = 'Item Peminjaman';
            }

            $whatsApp->notifyDeadlinePengembalian([
                'booking_id'     => $booking->id,
                'no_wa'          => $booking->no_wa,
                'nama_peminjam'  => $booking->nama_peminjam,
                'nama_item'      => $namaItems,
                'tanggal_kembali'=> $today->format('d M Y'),
            ]);

            // Tandai sudah dikirim agar tidak dikirim berulang
            $booking->update(['reminder_sent' => true]);
        }
    }

    /**
     * Daftar semua pengguna terdaftar (non-admin).
     */
    public function usersIndex(Request $request)
    {
        $search = $request->query('search');

        $query = User::whereNotIn('email', ['admin@staimas.com', 'yoga@staimas.com'])
            ->withCount('bookings')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * Reset password user oleh admin.
     */
    public function userResetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', "Password akun {$user->name} berhasil diubah.");
    }

    /**
     * Hapus akun user.
     */
    public function userDestroy(User $user)
    {
        $name = $user->name;
        $user->delete();
        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }
}

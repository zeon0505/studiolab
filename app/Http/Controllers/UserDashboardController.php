<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Jika admin mengakses dashboard user biasa, alihkan ke dashboard admin
        if (in_array($user->email, ['admin@staimas.com', 'yoga@staimas.com'])) {
            return redirect()->route('admin.dashboard');
        }

        $query = Booking::with('items', 'penanggungJawab')
            ->where('user_id', $user->id);

        // Filter by status jika ada
        $filter = $request->query('filter');
        if ($filter && in_array($filter, ['pending', 'disetujui', 'ditolak', 'selesai'])) {
            $query->where('status', $filter);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('user.dashboard', compact('bookings'));
    }

    public function itemsIndex(Request $request)
    {
        $tipe = $request->query('tipe', 'ruangan'); // default ke ruangan jika tidak dispesifikasikan
        $items = Item::where('tipe', $tipe)->latest()->get();
        return view('user.items', compact('items', 'tipe'));
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
}

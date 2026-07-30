<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        $totalItems = Item::count();
        $availableItems = Item::where('status', 'tersedia')->count();
        $totalBookings = \App\Models\Booking::count();

        return view('welcome', compact('totalItems', 'availableItems', 'totalBookings'));
    }

    public function peralatan(Request $request)
    {
        $kategori = $request->query('kategori'); // studio / laboratorium
        $tipe = $request->query('tipe'); // peralatan / ruangan

        $query = Item::query();

        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        $items = $query->latest()->get();

        return view('pages.peralatan', compact('items', 'kategori', 'tipe'));
    }

    public function alur()
    {
        return view('pages.alur');
    }

    public function struktur()
    {
        return view('pages.struktur');
    }

    public function peminjamanRuangan()
    {
        return view('pages.peminjaman_ruangan');
    }

    public function peminjamanPeralatan()
    {
        return view('pages.peminjaman_peralatan');
    }

    public function peminjamanForm(Item $item)
    {
        return view('pages.peminjaman_form', compact('item'));
    }

    public function kalender(Request $request)
    {
        $tipe = $request->query('tipe', 'ruangan');
        $bulan = $request->query('bulan', Carbon::now()->format('Y-m'));

        $startOfMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // Ambil semua booking di bulan ini
        $bookings = Booking::with('item')
            ->whereHas('item', fn($q) => $q->where('tipe', $tipe))
            ->whereIn('status', ['pending', 'disetujui'])
            ->whereBetween('tanggal_peminjaman', [$startOfMonth, $endOfMonth])
            ->get();

        // Build calendar data: ['2026-07-15' => [booking1, booking2]]
        $calendarData = [];
        foreach ($bookings as $b) {
            $date = $b->tanggal_peminjaman->format('Y-m-d');
            $calendarData[$date][] = $b;
        }

        $items = Item::where('tipe', $tipe)->where('status', 'tersedia')->get();
        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $startOfMonth->copy()->addMonth()->format('Y-m');

        return view('pages.kalender', compact(
            'tipe', 'bulan', 'startOfMonth', 'endOfMonth',
            'calendarData', 'items', 'prevMonth', 'nextMonth'
        ));
    }
}

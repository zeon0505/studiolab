<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}

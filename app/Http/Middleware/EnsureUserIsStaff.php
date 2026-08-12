<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Izinkan jika is_staff bernilai true ATAU menggunakan email admin utama
        if ($user->is_staff || in_array($user->email, ['admin@staimas.com', 'yoga@staimas.com'])) {
            return $next($request);
        }

        // Jika bukan staff/admin, alihkan ke dashboard user biasa
        return redirect()->route('user.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
    }
}

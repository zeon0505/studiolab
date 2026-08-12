<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserDashboardController;

// Halaman Publik
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/peralatan', [PageController::class, 'peralatan'])->name('pages.peralatan');
Route::get('/alur', [PageController::class, 'alur'])->name('pages.alur');
Route::get('/struktur', [PageController::class, 'struktur'])->name('pages.struktur');
Route::get('/kalender', [PageController::class, 'kalender'])->name('pages.kalender');

// Sitemap XML untuk Google Indexing
Route::get('/sitemap.xml', function() {
    $urls = [
        '/',
        '/peralatan',
        '/alur',
        '/struktur',
        '/kalender'
    ];
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    foreach ($urls as $url) {
        $xml .= '<url>';
        $xml .= '<loc>' . url($url) . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>' . ($url === '/' ? '1.0' : '0.8') . '</priority>';
        $xml .= '</url>';
    }
    
    $xml .= '</urlset>';
    
    return response($xml, 200, [
        'Content-Type' => 'application/xml'
    ]);
});

// Autentikasi Pengguna (User & Admin Terpadu)
Route::get('/login', [UserAuthController::class, 'loginForm'])->name('login');
Route::post('/login', [UserAuthController::class, 'login'])->name('login.post');
Route::get('/register', [UserAuthController::class, 'registerForm'])->name('register');
Route::post('/register', [UserAuthController::class, 'register'])->name('register.post');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');

// Dashboard User & Fitur Terproteksi Auth
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    
    // Peminjaman Terproteksi Login
    Route::get('/peminjaman/ruangan', [PageController::class, 'peminjamanRuangan'])->name('pages.peminjaman.ruangan');
    Route::get('/peminjaman/peralatan', [PageController::class, 'peminjamanPeralatan'])->name('pages.peminjaman.peralatan');
    Route::get('/peminjaman/form/{item}', [PageController::class, 'peminjamanForm'])->name('pages.peminjaman.form');

    // CRUD Inventaris for User
    Route::get('/dashboard/items', [UserDashboardController::class, 'itemsIndex'])->name('user.items.index');
    Route::post('/dashboard/items/store', [UserDashboardController::class, 'itemsStore'])->name('user.items.store');
    Route::put('/dashboard/items/{item}/update', [UserDashboardController::class, 'itemsUpdate'])->name('user.items.update');
    Route::delete('/dashboard/items/{item}/delete', [UserDashboardController::class, 'itemsDestroy'])->name('user.items.destroy');
});

// Redirect URL Admin yang sering diketik manual agar tidak 404
Route::redirect('/admin', '/admin/dashboard');
Route::redirect('/admin/login', '/login');

// Dashboard Admin (Terproteksi Auth & Admin Email)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/history', [AdminController::class, 'history'])->name('history');
    
    // Manajemen Inventaris Item
    Route::get('/items', [AdminController::class, 'itemsIndex'])->name('items.index');
    Route::post('/items/store', [AdminController::class, 'itemsStore'])->name('items.store');
    Route::put('/items/{item}/update', [AdminController::class, 'itemsUpdate'])->name('items.update');
    Route::delete('/items/{item}/delete', [AdminController::class, 'itemsDestroy'])->name('items.destroy');
    
    // Konfirmasi Booking Peminjaman
    Route::get('/bookings/export-pdf', [AdminController::class, 'exportPdf'])->name('bookings.export-pdf');
    Route::get('/bookings/{booking}', [AdminController::class, 'bookingShow'])->name('bookings.show');
    Route::post('/bookings/{booking}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.status');
    
    // Penanggung Jawab Harian (PJ)
    Route::get('/assignments', [AdminController::class, 'assignmentsIndex'])->name('assignments.index');
    Route::post('/assignments/store', [AdminController::class, 'assignmentsStore'])->name('assignments.store');
    Route::delete('/assignments/{assignment}/delete', [AdminController::class, 'assignmentsDestroy'])->name('assignments.destroy');

    // Scan QR Code
    Route::get('/scan-qr', fn() => view('admin.scan_qr'))->name('scan-qr');

    // Update & Tambah Pengguna / PJ
    Route::post('/users/store', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{user}/update-wa', [AdminController::class, 'updateUserWa'])->name('users.update-wa');
});

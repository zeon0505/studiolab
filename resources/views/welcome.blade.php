@extends('layouts.app')
@section('title', 'Beranda')

@section('content')

{{-- HERO SECTION --}}
<section class="bg-gradient-to-br from-teal-900 via-teal-800 to-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-20 sm:py-28 flex flex-col lg:flex-row items-center gap-12">
        <div class="flex-1 space-y-6">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-teal-200 text-[11px] font-semibold px-3 py-1.5 rounded-full">
                <span class="w-1.5 h-1.5 bg-teal-400 rounded-full animate-pulse"></span>
                UPT Studio & Lab STAIMAS Wonogiri
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight">
                Pinjam Peralatan &<br>
                <span class="text-teal-300">Ruangan Studio</span><br>
                Secara Online
            </h1>
            <p class="text-[15px] text-teal-100 leading-relaxed max-w-lg">
                Sistem peminjaman digital UPT STAIMAS Wonogiri. Ajukan peminjaman peralatan studio atau ruangan laboratorium kapan saja, pantau status persetujuan secara real-time.
            </p>
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ route('pages.peminjaman.ruangan') }}"
                   class="inline-flex items-center gap-2 bg-teal-400 hover:bg-teal-300 text-teal-950 font-bold px-6 py-3 rounded-xl text-[13px] transition-all shadow-lg shadow-teal-900/30">
                    <i class="fas fa-calendar-plus"></i> Ajukan Peminjaman
                </a>
                <a href="{{ route('pages.peralatan') }}"
                   class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold px-6 py-3 rounded-xl text-[13px] transition-all">
                    <i class="fas fa-box-open"></i> Lihat Inventaris
                </a>
            </div>
        </div>
        <div class="flex-1 grid grid-cols-2 gap-3 max-w-sm">
            <div class="bg-white/10 border border-white/20 rounded-2xl p-5 space-y-2">
                <div class="w-9 h-9 bg-teal-400/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-video text-teal-300 text-sm"></i>
                </div>
                <p class="font-bold text-white text-[13px]">Studio Siaran</p>
                <p class="text-[11px] text-teal-200">Podcast & Recording</p>
            </div>
            <div class="bg-white/10 border border-white/20 rounded-2xl p-5 space-y-2">
                <div class="w-9 h-9 bg-blue-400/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-flask text-blue-300 text-sm"></i>
                </div>
                <p class="font-bold text-white text-[13px]">Laboratorium</p>
                <p class="text-[11px] text-teal-200">Terpadu & Modern</p>
            </div>
            <div class="bg-white/10 border border-white/20 rounded-2xl p-5 space-y-2">
                <div class="w-9 h-9 bg-amber-400/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-camera text-amber-300 text-sm"></i>
                </div>
                <p class="font-bold text-white text-[13px]">Peralatan</p>
                <p class="text-[11px] text-teal-200">Kamera, Mikrofon dll</p>
            </div>
            <div class="bg-teal-400/20 border border-teal-400/30 rounded-2xl p-5 space-y-2">
                <div class="w-9 h-9 bg-teal-400/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-teal-300 text-sm"></i>
                </div>
                <p class="font-bold text-white text-[13px]">Booking Jam</p>
                <p class="text-[11px] text-teal-200">Pilih jam manual</p>
            </div>
        </div>
    </div>
</section>

{{-- STATS BAR --}}
<section class="border-b border-slate-100 bg-white">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $totalItems }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Item Inventaris</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-teal-600">{{ $availableItems }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Tersedia Dipinjam</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $totalBookings }}</p>
                <p class="text-[12px] text-slate-400 mt-1">Total Peminjaman</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">6</p>
                <p class="text-[12px] text-slate-400 mt-1">Hari Operasional</p>
            </div>
        </div>
    </div>
</section>

{{-- CARA PINJAM --}}
<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-slate-900">Cara Meminjam</h2>
            <p class="text-[13px] text-slate-500 mt-2">Proses peminjaman sederhana, cepat, dan transparan</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['1', 'fa-user-plus', 'teal', 'Daftar / Login', 'Buat akun mahasiswa atau dosen untuk mulai mengajukan peminjaman.'],
                ['2', 'fa-calendar-check', 'blue', 'Pilih & Isi Form', 'Pilih item atau ruangan, tentukan tanggal dan jam pinjam, unggah KTM/KTP.'],
                ['3', 'fa-clock-rotate-left', 'amber', 'Tunggu Konfirmasi', 'Admin UPT akan memproses dan mengkonfirmasi permohonan Anda.'],
                ['4', 'fa-circle-check', 'emerald', 'Pinjam & Kembalikan', 'Datang sesuai jadwal dan kembalikan tepat waktu setelah selesai.'],
            ] as [$num, $icon, $color, $title, $desc])
                <div class="bg-white rounded-2xl border border-slate-200 p-6 relative">
                    <div class="w-8 h-8 bg-{{ $color }}-50 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas {{ $icon }} text-{{ $color }}-500 text-sm"></i>
                    </div>
                    <span class="absolute top-4 right-4 text-[11px] font-bold text-slate-200">{{ $num }}</span>
                    <h3 class="font-bold text-slate-900 text-[14px] mb-1.5">{{ $title }}</h3>
                    <p class="text-[12px] text-slate-500 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- KATEGORI LAYANAN --}}
<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-teal-600 to-teal-800 rounded-3xl p-8 text-white">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-video text-white text-lg"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Studio Penyiaran & Podcast</h3>
                <p class="text-teal-200 text-[13px] leading-relaxed mb-5">Ruangan studio professional dengan perlengkapan siaran, rekaman podcast, dan produksi konten digital.</p>
                <a href="{{ route('pages.peralatan') }}"
                   class="inline-flex items-center gap-2 bg-white text-teal-700 font-bold text-[12px] px-4 py-2 rounded-xl hover:bg-teal-50 transition-colors">
                    Lihat Inventaris <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-3xl p-8 text-white">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-5">
                    <i class="fas fa-flask text-white text-lg"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Laboratorium Terpadu</h3>
                <p class="text-slate-300 text-[13px] leading-relaxed mb-5">Laboratorium modern untuk kegiatan praktikum, penelitian, dan eksplorasi ilmu pengetahuan mahasiswa.</p>
                <a href="{{ route('pages.peralatan') }}"
                   class="inline-flex items-center gap-2 bg-white text-slate-700 font-bold text-[12px] px-4 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                    Lihat Inventaris <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA SECTION --}}
<section class="py-16 bg-slate-50 border-t border-slate-100">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-slate-900 mb-3">Siap Mengajukan Peminjaman?</h2>
        <p class="text-[13px] text-slate-500 mb-6">Login atau daftar akun untuk mulai mengajukan peminjaman secara online.</p>
        <div class="flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ route('pages.peminjaman.ruangan') }}"
                   class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold px-6 py-3 rounded-xl text-[13px] transition-colors shadow-sm">
                    <i class="fas fa-calendar-plus"></i> Ajukan Sekarang
                </a>
                <a href="{{ route('user.dashboard') }}"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-6 py-3 rounded-xl text-[13px] transition-colors">
                    <i class="fas fa-desktop"></i> Dashboard Saya
                </a>
            @else
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-bold px-6 py-3 rounded-xl text-[13px] transition-colors shadow-sm">
                    <i class="fas fa-user-plus"></i> Daftar Akun Gratis
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold px-6 py-3 rounded-xl text-[13px] transition-colors">
                    <i class="fas fa-sign-in-alt"></i> Sudah Punya Akun
                </a>
            @endauth
        </div>
    </div>
</section>

@endsection

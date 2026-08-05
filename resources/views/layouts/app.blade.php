<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gema Studio') — Peminjaman Studio & Lab UPT STAIMAS Wonogiri</title>
    <meta name="description" content="Gema Studio — Portal Layanan Peminjaman Peralatan & Ruangan Studio/Laboratorium secara Online di UPT STAIMAS Wonogiri.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="bg-white text-slate-800 antialiased">

    {{-- TOP BAR --}}
    <div class="bg-teal-900 text-teal-100 text-[11px] py-2 px-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <span><i class="fas fa-phone mr-1.5 opacity-70"></i>+62 822-2320-4552</span>
                <span class="hidden sm:inline"><i class="fas fa-envelope mr-1.5 opacity-70"></i>info@staimaswonogiri.ac.id</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="https://staimaswonogiri.ac.id" target="_blank" class="hover:text-white transition-colors">staimaswonogiri.ac.id</a>
                <span class="text-teal-700">|</span>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="font-semibold hover:text-white transition-colors">
                        <i class="fas fa-user mr-1"></i>{{ Auth::user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- MAIN NAVBAR --}}
    <header class="sticky top-0 bg-white border-b border-slate-100 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div style="width: 55px; height: 55px; border-radius: 12px; overflow: hidden; background-color: #ffffff; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
                    <img src="{{ asset('logo-web.jpg') }}" alt="STAIMAS" style="width: 100%; height: 100%; object-fit: cover; transform: scale(1.15); transform-origin: center;">
                </div>
                <div class="leading-tight">
                    <span class="block text-[13px] font-bold text-slate-900 tracking-tight">Gema Studio</span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider" style="color: #0a5e50;">UPT STAIMAS Wonogiri</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-1">
                <a href="{{ route('home') }}"
                   class="px-3.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('home') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Beranda
                </a>
                <a href="{{ route('pages.peralatan') }}"
                   class="px-3.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('pages.peralatan') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Inventaris
                </a>
                <a href="{{ route('pages.alur') }}"
                   class="px-3.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('pages.alur') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Alur Pinjam
                </a>
                <a href="{{ route('pages.struktur') }}"
                   class="px-3.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('pages.struktur') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    Pengelola
                </a>
                <a href="{{ route('pages.kalender') }}"
                   class="px-3.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('pages.kalender') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                    <i class="fas fa-calendar-alt text-xs mr-1"></i> Kalender
                </a>

            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('user.dashboard') }}"
                       class="hidden sm:inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-semibold px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-desktop text-xs"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('pages.peralatan') }}"
                       class="hidden sm:inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-semibold px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-clipboard-list text-xs"></i> Ajukan Pinjam
                    </a>
                @endauth
                <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <div class="hidden lg:hidden border-t border-slate-100 bg-white" id="mobile-menu">
            <div class="max-w-6xl mx-auto px-4 py-3 space-y-1">
                @foreach([
                    ['home', 'fa-house', 'Beranda'],
                    ['pages.peralatan', 'fa-box-open', 'Inventaris'],
                    ['pages.alur', 'fa-route', 'Alur Pinjam'],
                    ['pages.struktur', 'fa-people-group', 'Pengelola'],
                    ['pages.kalender', 'fa-calendar-alt', 'Kalender'],
                ] as [$route, $icon, $label])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium
                              {{ request()->routeIs($route) ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                        <i class="fas {{ $icon }} w-4 text-center text-slate-400"></i> {{ $label }}
                    </a>
                @endforeach

                @auth
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-teal-700 bg-teal-50">
                        <i class="fas fa-desktop w-4 text-center text-teal-500"></i> Dashboard Akun
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-slate-900 text-slate-400 mt-16">
        <div class="max-w-6xl mx-auto px-4 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-video text-white text-xs"></i>
                        </div>
                        <span class="font-bold text-white text-[13px]">UPT Studiolab STAIMAS</span>
                    </div>
                    <p class="text-[12px] leading-relaxed">Unit Pelaksana Teknis Studio & Laboratorium STAIMAS Wonogiri. Fasilitas modern untuk mendukung kegiatan akademik mahasiswa.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-[13px] mb-3">Navigasi</h4>
                    <ul class="space-y-2 text-[12px]">
                        <li><a href="{{ route('pages.peralatan') }}" class="hover:text-white transition-colors">Inventaris & Peminjaman</a></li>
                        <li><a href="{{ route('pages.alur') }}" class="hover:text-white transition-colors">Alur Peminjaman</a></li>
                        <li><a href="{{ route('pages.struktur') }}" class="hover:text-white transition-colors">Struktur Pengelola</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white text-[13px] mb-3">Kontak UPT</h4>
                    <ul class="space-y-2 text-[12px]">
                        <li><i class="fas fa-phone mr-2 text-teal-400"></i>+62 822-2320-4552</li>
                        <li><i class="fas fa-envelope mr-2 text-teal-400"></i>info@staimaswonogiri.ac.id</li>
                        <li><i class="fas fa-map-marker-alt mr-2 text-teal-400"></i>Wonogiri, Jawa Tengah</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-8 pt-6 text-center text-[11px]">
                <p>© {{ date('Y') }} UPT Studio & Laboratorium STAIMAS Wonogiri. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

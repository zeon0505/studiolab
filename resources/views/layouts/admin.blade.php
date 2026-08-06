<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Admin UPT Studiolab</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        /* Mobile Sidebar Custom CSS */
        @media (max-width: 767px) {
            #admin-sidebar {
                position: fixed !important;
                transform: translateX(-100%) !important;
                z-index: 50 !important;
                height: 100vh !important;
            }
            #admin-sidebar.active {
                transform: translateX(0) !important;
            }
            #mobile-sidebar-overlay.active {
                display: block !important;
            }
        }
        
        /* Desktop Sidebar Reset */
        @media (min-width: 768px) {
            #admin-sidebar {
                position: sticky !important;
                transform: translateX(0) !important;
                z-index: 40 !important;
                display: flex !important;
            }
            #mobile-sidebar-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 antialiased min-h-screen">

<div class="flex flex-col md:flex-row min-h-screen">

    {{-- ===== MOBILE HEADER ===== --}}
    <div class="md:hidden bg-slate-900 px-5 py-4 flex items-center justify-between sticky top-0 z-30 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg overflow-hidden bg-white/10 flex items-center justify-center shrink-0 border border-white/10">
                <img src="{{ asset('logo-web.jpg') }}" alt="STAIMAS" class="w-full h-full object-contain">
            </div>
            <div class="leading-tight">
                <span class="block text-[11px] font-bold text-white uppercase tracking-widest">UPT Studiolab</span>
                <span class="text-[9px] text-slate-400 font-medium">STAIMAS</span>
            </div>
        </div>
        <button id="mobile-sidebar-toggle" class="text-white hover:text-teal-400 p-1.5 focus:outline-none transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    {{-- ===== SIDEBAR ===== --}}
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-60 bg-slate-900 flex flex-col transform -translate-x-full md:translate-x-0 md:sticky md:top-0 md:h-screen transition-transform duration-300 ease-in-out">

        {{-- Brand (Mobile Close Button Included) --}}
        <div class="px-5 py-5 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-white/10 flex items-center justify-center shrink-0 border border-white/10">
                    <img src="{{ asset('logo-web.jpg') }}" alt="STAIMAS" class="w-full h-full object-contain">
                </div>
                <div class="leading-tight">
                    <span class="block text-[11px] font-bold text-white uppercase tracking-widest">UPT Studiolab</span>
                    <span class="text-[10px] text-slate-400 font-medium">STAIMAS Wonogiri</span>
                </div>
            </div>
            <button id="mobile-sidebar-close" class="md:hidden text-slate-400 hover:text-white p-1 focus:outline-none transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>


        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2">MANAJEMEN</p>

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.dashboard') ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-table-list w-4 text-center {{ request()->routeIs('admin.dashboard') ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>Daftar Peminjaman</span>
            </a>

            <a href="{{ route('admin.history') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.history') ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-history w-4 text-center {{ request()->routeIs('admin.history') ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>Riwayat Peminjaman</span>
            </a>

            <a href="{{ route('admin.items.index', ['tipe' => 'ruangan']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.items.*') && request('tipe') === 'ruangan' ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-door-open w-4 text-center {{ request()->routeIs('admin.items.*') && request('tipe') === 'ruangan' ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>Kelola Ruangan</span>
            </a>

            <a href="{{ route('admin.items.index', ['tipe' => 'peralatan']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.items.*') && request('tipe') === 'peralatan' ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-camera w-4 text-center {{ request()->routeIs('admin.items.*') && request('tipe') === 'peralatan' ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>Kelola Peralatan</span>
            </a>

            <a href="{{ route('admin.assignments.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.assignments.*') ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-user-clock w-4 text-center {{ request()->routeIs('admin.assignments.*') ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>PJ Harian</span>
            </a>

            <a href="{{ route('admin.scan-qr') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('admin.scan-qr') ? 'bg-teal-500 text-white font-semibold shadow-sm shadow-teal-900/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-qrcode w-4 text-center {{ request()->routeIs('admin.scan-qr') ? 'text-white' : 'text-slate-400' }}"></i>
                <span>Scan QR Code</span>
                <span class="ml-auto text-[8px] font-black px-1.5 py-0.5 rounded-md {{ request()->routeIs('admin.scan-qr') ? 'bg-white/20 text-white' : 'bg-teal-500/20 text-teal-400' }} uppercase tracking-wider">Baru</span>
            </a>

            <div class="pt-3 mt-3 border-t border-white/5">
                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2">LAINNYA</p>
                <a href="{{ route('home') }}" target="_blank"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium text-slate-400 hover:bg-white/5 hover:text-white transition-all duration-150">
                    <i class="fas fa-arrow-up-right-from-square w-4 text-center text-slate-400"></i>
                    <span>Lihat Website</span>
                </a>
            </div>
        </nav>

        {{-- User Info --}}
        <div class="px-3 py-4 border-t border-white/5">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-white/5">
                <div class="w-7 h-7 rounded-full bg-teal-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(Auth::user()?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[12px] font-semibold text-white block truncate">{{ Auth::user()?->name ?? 'Admin' }}</span>
                    <span class="text-[10px] text-slate-400 block">Administrator</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-500 hover:text-red-400 transition-colors text-xs">
                        <i class="fas fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Mobile Sidebar Overlay --}}
    <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300 ease-in-out md:hidden"></div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-grow flex flex-col min-w-0">

        {{-- Top Header Bar --}}
        <header class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between sticky top-0 md:top-0 z-20">
            <div>
                <h1 class="text-[14px] md:text-[15px] font-bold text-slate-900">@yield('title', 'Dashboard')</h1>
                <p class="hidden sm:block text-[11px] text-slate-400 font-medium mt-0.5">Portal UPT Studio & Lab STAIMAS Wonogiri</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] md:text-[11px] text-slate-500">
                    <i class="far fa-clock mr-1"></i>
                    {{ now()->translatedFormat('d M Y') }}
                </span>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-grow p-4 md:p-8">

            {{-- Flash Messages --}}
            @if(session()->has('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-[12px] p-3.5 rounded-xl flex items-center gap-3 mb-6">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session()->has('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 text-[12px] p-3.5 rounded-xl flex items-center gap-3 mb-6">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</div>

{{-- Sidebar Mobile Toggle JS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('mobile-sidebar-toggle');
        const closeBtn = document.getElementById('mobile-sidebar-close');
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');

        if (sidebar && overlay) {
            function openSidebar() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('overflow-hidden');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);
        }
    });
</script>

</body>
</html>

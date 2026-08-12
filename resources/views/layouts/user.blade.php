<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Saya') — UPT Studiolab</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        /* Desktop Sidebar Reset */
        @media (min-width: 768px) {
            #user-sidebar {
                position: sticky !important;
                top: 0 !important;
                left: 0 !important;
                transform: translateX(0) !important;
                z-index: 40 !important;
                display: flex !important;
                height: 100vh !important;
                width: 240px !important;
            }
            /* Paksa sembunyikan tombol close di desktop */
            #user-sidebar button {
                display: none !important;
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
                <span class="text-[9px] text-slate-400 font-medium">Portal Pengguna</span>
            </div>
        </div>
        <button onclick="document.getElementById('user-sidebar').classList.remove('-translate-x-full'); document.getElementById('user-sidebar').classList.add('translate-x-0'); document.getElementById('mobile-sidebar-overlay').classList.remove('hidden'); document.body.classList.add('overflow-hidden')" class="text-white hover:text-teal-400 p-1.5 focus:outline-none transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    {{-- ===== SIDEBAR ===== --}}
    <aside id="user-sidebar" class="fixed inset-y-0 left-0 z-50 w-60 bg-slate-900 flex flex-col transform -translate-x-full md:translate-x-0 md:sticky md:top-0 md:h-screen transition-transform duration-300 ease-in-out">

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
            <button onclick="document.getElementById('user-sidebar').classList.add('-translate-x-full'); document.getElementById('user-sidebar').classList.remove('translate-x-0'); document.getElementById('mobile-sidebar-overlay').classList.add('hidden'); document.body.classList.remove('overflow-hidden')" class="md:hidden text-slate-400 hover:text-white p-1 focus:outline-none transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2">MENU UTAMA</p>

            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-150
                      {{ request()->routeIs('user.dashboard') && !request()->has('tipe') ? 'bg-white text-slate-900 font-semibold shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="fas fa-history w-4 text-center {{ request()->routeIs('user.dashboard') && !request()->has('tipe') ? 'text-teal-700' : 'text-slate-400' }}"></i>
                <span>Riwayat Peminjaman</span>
            </a>



            <div class="pt-3 mt-3 border-t border-white/5">
                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 mb-2">LAINNYA</p>
                <a href="{{ route('home') }}"
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
                    {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[12px] font-semibold text-white block truncate">{{ Auth::user()?->name ?? 'User' }}</span>
                    <span class="text-[10px] text-slate-400 block">Mahasiswa / Dosen</span>
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
    <div id="mobile-sidebar-overlay" onclick="document.getElementById('user-sidebar').classList.add('-translate-x-full'); document.getElementById('user-sidebar').classList.remove('translate-x-0'); document.getElementById('mobile-sidebar-overlay').classList.add('hidden'); document.body.classList.remove('overflow-hidden')" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity duration-300 ease-in-out md:hidden"></div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-grow flex flex-col min-w-0">

        {{-- Top Header Bar --}}
        <header class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between sticky top-0 md:top-0 z-20">
            <div>
                <h1 class="text-[14px] md:text-[15px] font-bold text-slate-900">@yield('title', 'Dashboard Saya')</h1>
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

{{-- ===== CUSTOM CONFIRM MODAL (Global, Pure Inline CSS) ===== --}}
<div id="custom-confirm-modal" style="
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
">
    {{-- Backdrop click area --}}
    <div id="custom-confirm-backdrop" onclick="confirmCancel()" style="position:absolute;inset:0;"></div>

    {{-- Modal Box --}}
    <div id="custom-confirm-box" style="
        position: relative;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 25px 60px rgba(15,23,42,0.25), 0 8px 24px rgba(15,23,42,0.12);
        width: 100%;
        max-width: 400px;
        overflow: hidden;
        transform: scale(0.85) translateY(20px);
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
    ">
        {{-- Accent bar --}}
        <div id="custom-confirm-accent" style="height: 5px; background: linear-gradient(90deg, #ef4444, #f43f5e);"></div>

        {{-- Body --}}
        <div style="padding: 28px 28px 24px;">
            {{-- Header row --}}
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div id="custom-confirm-icon-wrap" style="
                    width: 52px; height: 52px; border-radius: 16px;
                    background: #fee2e2;
                    display: flex; align-items: center; justify-content: center;
                    flex-shrink: 0;
                ">
                    <i id="custom-confirm-icon" class="fas fa-trash-alt" style="color: #ef4444; font-size: 20px;"></i>
                </div>
                <div>
                    <p id="custom-confirm-title" style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">Hapus Data</p>
                    <p style="font-size: 12px; color: #94a3b8; margin: 4px 0 0; font-weight: 500;">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            {{-- Message --}}
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px 18px; margin-bottom: 24px;">
                <p id="custom-confirm-message" style="font-size: 14px; color: #334155; margin: 0; line-height: 1.6; font-weight: 500;"></p>
            </div>

            {{-- Buttons --}}
            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="confirmCancel()" style="
                    flex: 1; padding: 13px 0; border-radius: 14px;
                    border: 2px solid #e2e8f0; background: #f8fafc;
                    font-size: 14px; font-weight: 700; color: #475569;
                    cursor: pointer; transition: all 0.15s ease;
                    font-family: inherit;
                " onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                    Batal
                </button>
                <button type="button" id="custom-confirm-ok-btn" onclick="confirmOk()" style="
                    flex: 1; padding: 13px 0; border-radius: 14px;
                    border: none;
                    background: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%);
                    font-size: 14px; font-weight: 700; color: #ffffff;
                    cursor: pointer; transition: all 0.15s ease;
                    display: flex; align-items: center; justify-content: center; gap: 8px;
                    box-shadow: 0 4px 16px rgba(239,68,68,0.35);
                    font-family: inherit;
                " onmouseover="this.style.opacity='0.9';this.style.transform='scale(1.02)'" onmouseout="this.style.opacity='1';this.style.transform='scale(1)'">
                    <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    <span id="custom-confirm-ok-label">Hapus</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let _confirmCallback = null;
    const _themes = {
        danger: {
            accent: 'linear-gradient(90deg, #ef4444, #f43f5e)',
            iconBg: '#fee2e2',
            iconColor: '#ef4444',
            iconClass: 'fa-trash-alt',
            btnBg: 'linear-gradient(135deg, #ef4444 0%, #f43f5e 100%)',
            btnShadow: 'rgba(239,68,68,0.35)',
        },
        warning: {
            accent: 'linear-gradient(90deg, #f59e0b, #fbbf24)',
            iconBg: '#fef3c7',
            iconColor: '#d97706',
            iconClass: 'fa-exclamation-triangle',
            btnBg: 'linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)',
            btnShadow: 'rgba(245,158,11,0.35)',
        },
        info: {
            accent: 'linear-gradient(90deg, #0d9488, #06b6d4)',
            iconBg: '#ccfbf1',
            iconColor: '#0d9488',
            iconClass: 'fa-info-circle',
            btnBg: 'linear-gradient(135deg, #0d9488 0%, #0891b2 100%)',
            btnShadow: 'rgba(13,148,136,0.35)',
        },
    };

    function showConfirm(message, callback, options = {}) {
        const type = options.type || 'danger';
        const theme = _themes[type] || _themes.danger;
        const modal = document.getElementById('custom-confirm-modal');
        const box   = document.getElementById('custom-confirm-box');

        // Apply content
        document.getElementById('custom-confirm-message').textContent = message;
        document.getElementById('custom-confirm-title').textContent = options.title || 'Konfirmasi Hapus';
        document.getElementById('custom-confirm-ok-label').textContent = options.okLabel || 'Hapus';

        // Apply theme
        document.getElementById('custom-confirm-accent').style.background = theme.accent;
        const iconWrap = document.getElementById('custom-confirm-icon-wrap');
        iconWrap.style.background = theme.iconBg;
        const icon = document.getElementById('custom-confirm-icon');
        icon.className = 'fas ' + theme.iconClass;
        icon.style.color = theme.iconColor;
        const okBtn = document.getElementById('custom-confirm-ok-btn');
        okBtn.style.background = theme.btnBg;
        okBtn.style.boxShadow = '0 4px 16px ' + theme.btnShadow;
        // Update icon in button
        okBtn.querySelector('i').className = 'fas ' + (options.okIcon || theme.iconClass);
        okBtn.querySelector('i').style.fontSize = '12px';

        _confirmCallback = callback;

        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Animate in
        box.style.transform = 'scale(0.85) translateY(20px)';
        box.style.opacity = '0';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                box.style.transform = 'scale(1) translateY(0)';
                box.style.opacity = '1';
            });
        });
    }

    function confirmOk() {
        _closeConfirmModal(() => {
            if (_confirmCallback) _confirmCallback();
        });
    }

    function confirmCancel() {
        _closeConfirmModal();
    }

    function _closeConfirmModal(after) {
        const modal = document.getElementById('custom-confirm-modal');
        const box   = document.getElementById('custom-confirm-box');
        box.style.transform = 'scale(0.85) translateY(20px)';
        box.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            if (after) after();
        }, 250);
    }

    function closeSidebar() {
        const s = document.getElementById('user-sidebar');
        const o = document.getElementById('mobile-sidebar-overlay');
        if (s) { s.classList.add('-translate-x-full'); s.classList.remove('translate-x-0'); }
        if (o) o.classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>

</body>
</html>

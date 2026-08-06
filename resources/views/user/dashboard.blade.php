@extends('layouts.app')
@section('title', 'Dashboard Saya')

@section('content')
<div class="min-h-screen bg-slate-50">

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-teal-900 via-teal-800 to-teal-700 text-white px-4 pt-10 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image:url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
        <div class="max-w-4xl mx-auto relative z-10">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-teal-300 text-xs font-semibold uppercase tracking-widest mb-1">Dashboard Peminjaman</p>
                    <h1 class="text-2xl font-black">Halo, {{ Auth::user()->name }} 👋</h1>
                    <p class="text-teal-200 text-sm mt-1 opacity-80">Pantau semua status permohonan Anda di sini</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('pages.peminjaman.peralatan') }}"
                       class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-xs font-bold px-4 py-2.5 rounded-xl border border-white/20 transition-all backdrop-blur-sm">
                        <i class="fas fa-tools text-xs"></i> Peralatan
                    </a>
                    <a href="{{ route('pages.peminjaman.ruangan') }}"
                       class="inline-flex items-center gap-2 bg-white text-teal-800 hover:bg-teal-50 text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-lg shadow-black/10">
                        <i class="fas fa-door-open text-xs"></i> Ruangan
                    </a>
                </div>
            </div>

            {{-- Stats Strip --}}
            @php
                $allBookings = \App\Models\Booking::where('user_id', Auth::id())->get();
                $cPending   = $allBookings->where('status','pending')->count();
                $cDisetujui = $allBookings->where('status','disetujui')->count();
                $cDitolak   = $allBookings->where('status','ditolak')->count();
                $cSelesai   = $allBookings->where('status','selesai')->count();
            @endphp
            <div class="grid grid-cols-4 gap-3 mt-8">
                @foreach([
                    ['Menunggu', $cPending,   'fas fa-clock',       'text-amber-300',   'amber'],
                    ['Disetujui',$cDisetujui, 'fas fa-check-circle','text-emerald-300', 'emerald'],
                    ['Ditolak',  $cDitolak,   'fas fa-times-circle','text-red-300',     'red'],
                    ['Selesai',  $cSelesai,   'fas fa-flag-checkered','text-blue-300',  'blue'],
                ] as [$label, $count, $icon, $iconColor, $color])
                <div class="bg-white/10 border border-white/15 rounded-2xl p-4 text-center backdrop-blur-sm">
                    <i class="{{ $icon }} {{ $iconColor }} text-xl mb-2 block"></i>
                    <p class="text-2xl font-black">{{ $count }}</p>
                    <p class="text-teal-200 text-[10px] font-semibold mt-0.5">{{ $label }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 -mt-12 pb-16 relative z-10">

        @if(session()->has('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-4 rounded-2xl flex items-center gap-3 mb-6 shadow-sm">
                <i class="fas fa-check-circle text-emerald-400 text-lg shrink-0"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter Tabs --}}
        @php
            $activeFilter = request('filter', 'semua');
            $tabs = ['semua'=>'Semua', 'pending'=>'Menunggu', 'disetujui'=>'Disetujui', 'ditolak'=>'Ditolak', 'selesai'=>'Selesai'];
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-1.5 flex gap-1 overflow-x-auto mb-5">
            @foreach($tabs as $key => $label)
                <a href="{{ route('user.dashboard', $key !== 'semua' ? ['filter' => $key] : []) }}"
                   class="shrink-0 flex-1 text-center py-2 px-3 rounded-xl text-xs font-bold transition-all
                          {{ $activeFilter === $key ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20' : 'text-slate-500 hover:bg-slate-50' }}">
                    {{ $label }}
                    @if($key !== 'semua')
                        @php $cnt = $allBookings->where('status', $key)->count(); @endphp
                        @if($cnt > 0)
                        <span class="ml-1 {{ $activeFilter === $key ? 'bg-white/20' : 'bg-slate-100' }} text-[10px] rounded-full px-1.5 py-0.5">{{ $cnt }}</span>
                        @endif
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Booking Cards --}}
        @if($bookings->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-16 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-list text-slate-300 text-3xl"></i>
                </div>
                <p class="font-bold text-slate-400 mb-1">Belum ada riwayat peminjaman</p>
                <p class="text-slate-400 text-xs mb-5">Ajukan peminjaman pertama Anda sekarang!</p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('pages.peminjaman.ruangan') }}" class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition-colors">
                        <i class="fas fa-door-open"></i> Pinjam Ruangan
                    </a>
                    <a href="{{ route('pages.peminjaman.peralatan') }}" class="inline-flex items-center gap-2 border border-teal-600 text-teal-700 hover:bg-teal-50 text-xs font-bold px-5 py-2.5 rounded-xl transition-colors">
                        <i class="fas fa-tools"></i> Pinjam Peralatan
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach($bookings as $booking)
                @php
                    $statusConfig = [
                        'pending'   => ['bg'=>'bg-amber-50',   'border'=>'border-amber-200',   'badge'=>'bg-amber-100 text-amber-700',   'dot'=>'bg-amber-400 animate-pulse', 'icon'=>'fas fa-clock',        'label'=>'Menunggu Review'],
                        'disetujui' => ['bg'=>'bg-emerald-50', 'border'=>'border-emerald-200', 'badge'=>'bg-emerald-100 text-emerald-700','dot'=>'bg-emerald-500',             'icon'=>'fas fa-check-circle', 'label'=>'Disetujui'],
                        'ditolak'   => ['bg'=>'bg-red-50',     'border'=>'border-red-200',     'badge'=>'bg-red-100 text-red-700',       'dot'=>'bg-red-400',                 'icon'=>'fas fa-times-circle', 'label'=>'Ditolak'],
                        'selesai'   => ['bg'=>'bg-slate-50',   'border'=>'border-slate-200',   'badge'=>'bg-slate-100 text-slate-600',   'dot'=>'bg-slate-400',               'icon'=>'fas fa-flag-checkered','label'=>'Selesai'],
                    ];
                    $sc = $statusConfig[$booking->status] ?? $statusConfig['pending'];
                    $firstItem = $booking->items->first();
                    $isRuangan = $booking->items->where('tipe', 'ruangan')->count() > 0;
                @endphp
                <div class="bg-white rounded-3xl border {{ $sc['border'] }} shadow-sm overflow-hidden hover:shadow-md transition-shadow">

                    {{-- Card Header --}}
                    <div class="flex items-start gap-4 p-5">
                        {{-- Icon Tipe --}}
                        <div class="w-12 h-12 rounded-2xl {{ $isRuangan ? 'bg-blue-100' : 'bg-teal-100' }} flex items-center justify-center shrink-0">
                            <i class="{{ $isRuangan ? 'fas fa-door-open text-blue-600' : 'fas fa-tools text-teal-600' }} text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    @if($booking->items->count() > 0)
                                        @foreach($booking->items as $itm)
                                        <p class="font-bold text-slate-900 text-sm leading-snug">
                                            {{ $itm->nama }}
                                            @if($itm->pivot->jumlah > 1)
                                            <span class="text-[10px] font-bold text-teal-600 bg-teal-50 px-1.5 py-0.5 rounded-md ml-1">{{ $itm->pivot->jumlah }}x</span>
                                            @endif
                                        </p>
                                        @endforeach
                                        @if($firstItem)
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] font-bold uppercase text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">{{ $firstItem->kategori }}</span>
                                            <span class="text-[10px] font-bold uppercase text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">{{ $firstItem->tipe }}</span>
                                        </div>
                                        @endif
                                    @else
                                        <p class="font-bold text-slate-900 text-sm leading-snug">Peminjaman Ruangan</p>
                                    @endif
                                </div>
                                {{-- Status Badge --}}
                                <span class="shrink-0 inline-flex items-center gap-1.5 {{ $sc['badge'] }} text-[10px] font-bold px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </div>

                            {{-- Info Strip --}}
                            <div class="mt-3 flex flex-wrap gap-3">
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <i class="fas fa-calendar-alt text-teal-500 text-xs"></i>
                                    {{ $booking->tanggal_peminjaman->translatedFormat('d M Y') }}
                                </div>
                                @if($booking->jam_mulai)
                                <div class="flex items-center gap-1.5 text-xs font-bold text-teal-700">
                                    <i class="far fa-clock text-xs"></i>
                                    {{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB
                                </div>
                                @elseif($booking->tanggal_pengembalian)
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <i class="fas fa-calendar-check text-slate-400 text-xs"></i>
                                    s/d {{ $booking->tanggal_pengembalian->translatedFormat('d M Y') }}
                                </div>
                                @endif
                                @if($booking->penanggungJawab)
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <i class="fas fa-user-shield text-slate-400 text-xs"></i>
                                    {{ $booking->penanggungJawab->name }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Ditolak: tampilkan alasan --}}
                    @if($booking->status === 'ditolak' && $booking->catatan)
                    <div class="mx-5 mb-4 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                        <p class="text-[10px] font-bold uppercase text-red-400 tracking-wider mb-1">Alasan Penolakan</p>
                        <p class="text-xs text-red-700">{{ $booking->catatan }}</p>
                    </div>
                    @endif

                    {{-- Disetujui: QR Code bukti --}}
                    @if(in_array($booking->status, ['disetujui', 'selesai']))
                    <div class="mx-5 mb-4 bg-emerald-50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4">
                        {{-- QR Code via Google Charts API --}}
                        @php
                            $qrData = urlencode(route('admin.bookings.show', $booking->id));
                        @endphp
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ $qrData }}"
                             alt="QR Bukti #{{ $booking->id }}"
                             class="w-16 h-16 rounded-lg border-2 border-white shadow-sm">
                        <div>
                            <p class="text-xs font-bold text-emerald-800">Bukti Persetujuan</p>
                            <p class="text-[10px] text-emerald-600 mt-0.5">Tunjukkan QR ini kepada PJ saat pengambilan.</p>
                            <p class="text-[10px] font-mono text-emerald-500 mt-1 bg-emerald-100 px-2 py-0.5 rounded-md inline-block">#BKG-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Pending: timeline progress --}}
                    @if($booking->status === 'pending')
                    <div class="mx-5 mb-4 flex items-center gap-0">
                        @foreach(['Dikirim', 'Review PJ', 'Disetujui'] as $i => $step)
                        <div class="flex items-center gap-0 flex-1">
                            <div class="flex flex-col items-center">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black
                                    {{ $i === 0 ? 'bg-teal-600 text-white' : ($i === 1 ? 'bg-amber-400 text-white animate-pulse' : 'bg-slate-100 text-slate-300') }}">
                                    {{ $i === 0 ? '✓' : ($i === 1 ? '⋯' : ($i+1)) }}
                                </div>
                                <span class="text-[9px] text-slate-400 mt-1 font-medium">{{ $step }}</span>
                            </div>
                            @if($i < 2)
                            <div class="flex-1 h-0.5 {{ $i === 0 ? 'bg-teal-300' : 'bg-slate-100' }} mb-3.5 mx-1"></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
            <div class="mt-6 bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3">
                {{ $bookings->links() }}
            </div>
            @endif
        @endif

        {{-- Quick Actions --}}
        <div class="mt-8 grid grid-cols-2 gap-4">
            <a href="{{ route('pages.peminjaman.ruangan') }}"
               class="bg-gradient-to-br from-teal-600 to-teal-700 text-white rounded-3xl p-5 flex items-center gap-4 hover:from-teal-700 hover:to-teal-800 transition-all shadow-lg shadow-teal-600/20 group">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-door-open text-white"></i>
                </div>
                <div>
                    <p class="font-black text-sm">Pinjam Ruangan</p>
                    <p class="text-teal-200 text-[10px] mt-0.5">Studio & Lab tersedia</p>
                </div>
            </a>
            <a href="{{ route('pages.peminjaman.peralatan') }}"
               class="bg-gradient-to-br from-slate-700 to-slate-800 text-white rounded-3xl p-5 flex items-center gap-4 hover:from-slate-800 hover:to-slate-900 transition-all shadow-lg shadow-slate-700/20 group">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-tools text-white"></i>
                </div>
                <div>
                    <p class="font-black text-sm">Pinjam Peralatan</p>
                    <p class="text-slate-300 text-[10px] mt-0.5">Kamera, mic, tripod...</p>
                </div>
            </a>
        </div>

        {{-- Footer action --}}
        <div class="mt-6 text-center">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-arrow-right-from-bracket mr-1"></i> Keluar dari Akun
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

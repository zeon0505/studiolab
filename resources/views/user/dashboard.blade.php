@extends('layouts.user')
@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    @php
        $allBookings = \App\Models\Booking::where('user_id', Auth::id())->get();
        $cPending   = $allBookings->where('status','pending')->count();
        $cDisetujui = $allBookings->where('status','disetujui')->count();
        $cDitolak   = $allBookings->where('status','ditolak')->count();
        $cSelesai   = $allBookings->where('status','selesai')->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Menunggu</span>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $cPending }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu review PJ</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Disetujui</span>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $cDisetujui }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Disetujui & aktif</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ditolak</span>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $cDitolak }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Permohonan ditolak</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Selesai</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flag-checkered text-blue-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $cSelesai }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Selesai dikembalikan</p>
        </div>
    </div>

    {{-- Filter & Actions Row --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        {{-- Filter Tabs --}}
        @php
            $activeFilter = request('filter', 'semua');
            $tabs = ['semua'=>'Semua', 'pending'=>'Menunggu', 'disetujui'=>'Disetujui', 'ditolak'=>'Ditolak', 'selesai'=>'Selesai'];
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 p-1 flex gap-1 overflow-x-auto">
            @foreach($tabs as $key => $label)
                <a href="{{ route('user.dashboard', $key !== 'semua' ? ['filter' => $key] : []) }}"
                   class="shrink-0 text-center py-2 px-4 rounded-xl text-xs font-bold transition-all
                          {{ $activeFilter === $key ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                    {{ $label }}
                    @if($key !== 'semua')
                        @php $cnt = $allBookings->where('status', $key)->count(); @endphp
                        @if($cnt > 0)
                        <span class="ml-1 {{ $activeFilter === $key ? 'bg-white/20' : 'bg-slate-100' }} text-[9px] rounded-full px-1.5 py-0.5">{{ $cnt }}</span>
                        @endif
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Booking Quick Actions --}}
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('pages.peminjaman.ruangan') }}"
               class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all">
                <i class="fas fa-door-open text-xs"></i> Pinjam Ruangan
            </a>
            <a href="{{ route('pages.peminjaman.peralatan') }}"
               class="inline-flex items-center gap-2 border border-slate-200 hover:border-slate-300 bg-white text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl transition-all">
                <i class="fas fa-tools text-xs"></i> Pinjam Peralatan
            </a>
        </div>
    </div>

    {{-- Booking Cards / List --}}
    @if($bookings->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-16 text-center">
            <div class="w-16 h-16 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-slate-100">
                <i class="fas fa-clipboard-list text-slate-300 text-3xl"></i>
            </div>
            <p class="font-bold text-slate-400 mb-1">Belum ada riwayat peminjaman</p>
            <p class="text-slate-400 text-xs">Ajukan peminjaman pertama Anda melalui tombol di atas.</p>
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
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">

                {{-- Card Header --}}
                <div class="flex items-start gap-4 p-6">
                    {{-- Icon Tipe --}}
                    <div class="w-12 h-12 rounded-2xl {{ $isRuangan ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-teal-50 text-teal-600 border border-teal-100' }} flex items-center justify-center shrink-0">
                        <i class="{{ $isRuangan ? 'fas fa-door-open' : 'fas fa-tools' }} text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                            <div>
                                @if($booking->items->count() > 0)
                                    @foreach($booking->items as $itm)
                                    <p class="font-bold text-slate-900 text-base leading-snug">
                                        {{ $itm->nama }}
                                        @if($itm->pivot->jumlah > 1)
                                        <span class="text-[10px] font-bold text-teal-600 bg-teal-50 px-1.5 py-0.5 rounded-md ml-1">{{ $itm->pivot->jumlah }}x</span>
                                        @endif
                                    </p>
                                    @endforeach
                                    @if($firstItem)
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="text-[9px] font-extrabold uppercase text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md tracking-wider">{{ $firstItem->kategori }}</span>
                                        <span class="text-[9px] font-extrabold uppercase text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md tracking-wider">{{ $firstItem->tipe }}</span>
                                    </div>
                                    @endif
                                @else
                                    <p class="font-bold text-slate-900 text-base leading-snug">Peminjaman Ruangan</p>
                                @endif
                            </div>
                            {{-- Status Badge --}}
                            <span class="shrink-0 self-start inline-flex items-center gap-1.5 {{ $sc['badge'] }} text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                {{ $sc['label'] }}
                            </span>
                        </div>

                        {{-- Info Strip --}}
                        <div class="mt-4 flex flex-wrap gap-4 border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <i class="fas fa-calendar-alt text-teal-600"></i>
                                <span>Mulai: <strong>{{ $booking->tanggal_peminjaman->translatedFormat('d M Y') }}</strong></span>
                            </div>
                            @if($booking->jam_mulai)
                            <div class="flex items-center gap-2 text-xs text-teal-800 bg-teal-50 px-2.5 py-1 rounded-lg">
                                <i class="far fa-clock"></i>
                                <span>{{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB</span>
                            </div>
                            @elseif($booking->tanggal_pengembalian)
                            <div class="flex items-center gap-2 text-xs text-slate-600">
                                <i class="fas fa-calendar-check text-slate-400"></i>
                                <span>Kembali: <strong>{{ $booking->tanggal_pengembalian->translatedFormat('d M Y') }}</strong></span>
                            </div>
                            @endif
                            @if($booking->penanggungJawab)
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i class="fas fa-user-shield text-slate-400"></i>
                                <span>PJ: {{ $booking->penanggungJawab->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Ditolak: tampilkan alasan --}}
                @if($booking->status === 'ditolak' && $booking->catatan)
                <div class="mx-6 mb-5 bg-red-50 border border-red-100 rounded-2xl px-5 py-4">
                    <p class="text-[10px] font-black uppercase text-red-400 tracking-widest mb-1.5">Alasan Penolakan</p>
                    <p class="text-sm text-red-800 leading-relaxed">{{ $booking->catatan }}</p>
                </div>
                @endif

                {{-- Disetujui / Selesai: QR Code bukti --}}
                @if(in_array($booking->status, ['disetujui', 'selesai']))
                <div class="mx-6 mb-5 bg-emerald-50 border border-emerald-100 rounded-2xl p-5 flex items-center gap-5">
                    @php
                        $qrData = urlencode(route('admin.bookings.show', $booking->id));
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $qrData }}"
                         alt="QR Bukti #{{ $booking->id }}"
                         class="w-20 h-20 rounded-xl border-2 border-white shadow-sm shrink-0 bg-white">
                    <div>
                        <p class="text-sm font-black text-emerald-950">Bukti Persetujuan</p>
                        <p class="text-xs text-emerald-700 mt-1 leading-relaxed">Tunjukkan QR Code ini kepada PJ UPT saat mengambil barang/kunci ruangan.</p>
                        <span class="mt-2.5 font-mono text-[10px] font-black text-emerald-800 bg-emerald-100 border border-emerald-200 px-3 py-1 rounded-md inline-block uppercase tracking-wider">#BKG-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
                @endif

                {{-- Pending: timeline progress --}}
                @if($booking->status === 'pending')
                <div class="mx-6 mb-5 bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center justify-between gap-2">
                    @foreach(['Dikirim', 'Review PJ', 'Disetujui'] as $i => $step)
                    <div class="flex items-center gap-2 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black shrink-0
                                {{ $i === 0 ? 'bg-teal-600 text-white' : ($i === 1 ? 'bg-amber-400 text-white animate-pulse' : 'bg-slate-200 text-slate-400') }}">
                                {{ $i === 0 ? '✓' : ($i === 1 ? '⋯' : ($i+1)) }}
                            </div>
                            <span class="text-[10px] font-bold {{ $i === 0 ? 'text-teal-700' : ($i === 1 ? 'text-amber-600' : 'text-slate-400') }}">{{ $step }}</span>
                        </div>
                        @if($i < 2)
                        <div class="flex-1 h-[2px] {{ $i === 0 ? 'bg-teal-600' : 'bg-slate-200' }} min-w-[15px]"></div>
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
        <div class="mt-6 bg-white rounded-2xl border border-slate-200 px-4 py-3 shadow-sm">
            {{ $bookings->links() }}
        </div>
        @endif
    @endif

</div>
@endsection

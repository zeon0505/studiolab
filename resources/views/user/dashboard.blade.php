@extends('layouts.user')
@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="space-y-6">

    {{-- Notifikasi Session --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] font-medium px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-[13px] font-medium px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Row --}}
    @php
        $allBookings = \App\Models\Booking::where('user_id', Auth::id())->get();
        $cPending     = $allBookings->where('status','pending')->count();
        $cDisetujui   = $allBookings->where('status','disetujui')->count();
        $cDitolak     = $allBookings->where('status','ditolak')->count();
        $cSelesai     = $allBookings->where('status','selesai')->count();
        $cDibatalkan  = $allBookings->where('status','dibatalkan')->count();
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
            $tabs = ['semua'=>'Semua', 'pending'=>'Menunggu', 'disetujui'=>'Disetujui', 'ditolak'=>'Ditolak', 'selesai'=>'Selesai', 'dibatalkan'=>'Dibatalkan'];
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

        {{-- New Booking Buttons --}}
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('pages.peminjaman.ruangan') }}"
               class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-teal-200">
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
                    'pending'    => ['bg'=>'bg-amber-50',   'border'=>'border-amber-200',   'badge'=>'bg-amber-100 text-amber-700',   'dot'=>'bg-amber-400 animate-pulse', 'icon'=>'fas fa-clock',        'label'=>'Menunggu Review'],
                    'disetujui'  => ['bg'=>'bg-emerald-50', 'border'=>'border-emerald-200', 'badge'=>'bg-emerald-100 text-emerald-700','dot'=>'bg-emerald-500',             'icon'=>'fas fa-check-circle', 'label'=>'Disetujui'],
                    'ditolak'    => ['bg'=>'bg-red-50',     'border'=>'border-red-200',     'badge'=>'bg-red-100 text-red-700',       'dot'=>'bg-red-400',                 'icon'=>'fas fa-times-circle', 'label'=>'Ditolak'],
                    'selesai'    => ['bg'=>'bg-slate-50',   'border'=>'border-slate-200',   'badge'=>'bg-slate-100 text-slate-600',   'dot'=>'bg-slate-400',               'icon'=>'fas fa-flag-checkered','label'=>'Selesai'],
                    'dibatalkan' => ['bg'=>'bg-zinc-50',    'border'=>'border-zinc-200',    'badge'=>'bg-zinc-100 text-zinc-600',     'dot'=>'bg-zinc-400',                'icon'=>'fas fa-ban',          'label'=>'Dibatalkan'],
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
                                {{-- Tombol edit tanggal kembali — saat pending atau disetujui --}}
                                @if(in_array($booking->status, ['pending', 'disetujui']))
                                <button onclick="openEditTanggal({{ $booking->id }}, '{{ $booking->tanggal_pengembalian->format('Y-m-d') }}', '{{ $booking->tanggal_peminjaman->format('Y-m-d') }}')"
                                    class="ml-1 text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md transition-colors">
                                    <i class="fas fa-pencil-alt text-[9px]"></i> Edit
                                </button>
                                @endif
                            </div>
                            @endif
                            @if($booking->penanggungJawab)
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i class="fas fa-user-shield text-slate-400"></i>
                                <span>PJ: {{ $booking->penanggungJawab->name }}</span>
                            </div>
                            @endif
                            @if($booking->status === 'pending')
                            <div class="ml-auto">
                                <form action="{{ route('user.booking.cancel', $booking->id) }}" method="POST"
                                      id="cancel-form-{{ $booking->id }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button"
                                        onclick="openCancelModal({{ $booking->id }}, '{{ addslashes($booking->items->pluck('nama')->implode(', ') ?: 'Peminjaman ini') }}')"
                                        class="flex items-center gap-1.5 text-xs text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-lg transition-all font-bold">
                                    <i class="fas fa-ban text-[10px]"></i> Batalkan
                                </button>
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

                {{-- Upload Bukti Pengembalian --}}
                @if($booking->status === 'disetujui' && now()->startOfDay()->greaterThanOrEqualTo($booking->tanggal_pengembalian->startOfDay()))
                <div class="mx-6 mb-5 border border-indigo-200 rounded-2xl overflow-hidden" id="upload-section-{{ $booking->id }}">
                    {{-- Header --}}
                    <div class="bg-indigo-600 px-5 py-3 flex items-center gap-2">
                        <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-camera text-white text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[12px] font-black text-white">Unggah Bukti Pengembalian</p>
                            <p class="text-[10px] text-indigo-200">Foto barang yang sudah dikembalikan ke petugas</p>
                        </div>
                    </div>

                    <div class="bg-indigo-50/40 px-5 py-4">
                        @if($booking->foto_pengembalian)
                        {{-- Preview foto yang sudah diupload --}}
                        <div class="flex items-center gap-3 bg-white border border-indigo-100 rounded-xl p-3 mb-3">
                            <img src="{{ asset('storage/' . $booking->foto_pengembalian) }}"
                                 class="w-14 h-14 rounded-xl object-cover border border-indigo-100 shrink-0" alt="Bukti Pengembalian">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-4 h-4 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                                        <i class="fas fa-check text-emerald-600 text-[8px]"></i>
                                    </span>
                                    <p class="text-xs font-bold text-slate-800">Bukti sudah diunggah</p>
                                </div>
                                <p class="text-[10px] text-slate-500 leading-relaxed">Menunggu verifikasi petugas UPT untuk mengubah status menjadi Selesai.</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-indigo-600 font-semibold mb-2">Ganti foto jika diperlukan:</p>
                        @else
                        <p class="text-xs text-indigo-800 mb-3 leading-relaxed">
                            Jika barang sudah dikembalikan secara fisik kepada petugas UPT, unggah foto sebagai bukti untuk mempercepat verifikasi.
                        </p>
                        @endif

                        <form action="{{ route('user.booking.upload-bukti', $booking->id) }}"
                              method="POST" enctype="multipart/form-data"
                              id="upload-form-{{ $booking->id }}">
                            @csrf

                            {{-- Custom Drop Zone --}}
                            <label for="foto-input-{{ $booking->id }}"
                                   class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-indigo-200 rounded-xl bg-white p-5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all group"
                                   id="drop-label-{{ $booking->id }}">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-indigo-500 text-base"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs font-bold text-indigo-700" id="drop-text-{{ $booking->id }}">Klik untuk pilih foto</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, JPEG · Maks. 2MB</p>
                                </div>
                            </label>
                            <input type="file"
                                   name="foto_pengembalian"
                                   id="foto-input-{{ $booking->id }}"
                                   accept="image/*"
                                   required
                                   class="hidden"
                                   onchange="previewFoto(this, {{ $booking->id }})">

                            {{-- Preview sebelum submit --}}
                            <div id="preview-container-{{ $booking->id }}" class="hidden mt-3 flex items-center justify-between gap-3 bg-white border border-indigo-200 rounded-xl p-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img id="preview-img-{{ $booking->id }}" src="" alt="Preview" class="w-12 h-12 rounded-xl object-cover border border-indigo-100 shrink-0">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate" id="preview-name-{{ $booking->id }}"></p>
                                        <p class="text-[10px] text-slate-400">Siap dikirim</p>
                                    </div>
                                </div>
                                <button type="submit"
                                        style="background-color:#4f46e5"
                                        class="shrink-0 flex items-center gap-1.5 text-white text-xs font-bold px-4 py-2 rounded-xl hover:opacity-90 transition-all shadow-sm">
                                    <i class="fas fa-paper-plane text-[10px]"></i> Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                function previewFoto(input, bookingId) {
                    const file = input.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('preview-img-' + bookingId).src = e.target.result;
                        document.getElementById('preview-name-' + bookingId).textContent = file.name;
                        document.getElementById('preview-container-' + bookingId).classList.remove('hidden');
                        document.getElementById('drop-text-' + bookingId).textContent = '✓ Foto dipilih';
                        document.getElementById('drop-label-' + bookingId).classList.add('border-indigo-400', 'bg-indigo-50');
                    };
                    reader.readAsDataURL(file);
                }
                </script>
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

{{-- Modal Konfirmasi Pembatalan --}}
<div id="cancelModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div id="cancelModalBox"
         class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm z-10 overflow-hidden transition-all duration-200 scale-95 opacity-0">

        {{-- Header merah --}}
        <div class="bg-red-500 px-6 pt-6 pb-5 text-center">
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-ban text-white text-2xl"></i>
            </div>
            <h2 class="text-base font-black text-white">Batalkan Peminjaman?</h2>
            <p class="text-red-100 text-xs mt-1">Tindakan ini tidak dapat dibatalkan</p>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <p class="text-sm text-slate-600 text-center leading-relaxed mb-1">
                Anda akan membatalkan peminjaman:
            </p>
            <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-2.5 text-center mb-5">
                <p class="text-sm font-black text-red-800" id="cancel-item-name">Item</p>
            </div>

            <p class="text-xs text-slate-400 text-center leading-relaxed mb-5">
                Peminjaman akan langsung berstatus <span class="font-bold text-red-600">dibatalkan</span> dan admin akan mendapat notifikasi via WhatsApp.
            </p>

            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()"
                        class="flex-1 py-3 rounded-2xl border-2 border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 hover:border-slate-300 transition-all">
                    <i class="fas fa-arrow-left text-xs mr-1.5"></i> Kembali
                </button>
                <button type="button" id="confirm-cancel-btn"
                        style="background-color: #ef4444;"
                        class="flex-1 py-3 rounded-2xl text-white text-sm font-black hover:bg-red-600 transition-all shadow-md shadow-red-200 flex items-center justify-center gap-2">
                    <i class="fas fa-ban text-xs"></i> Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Tanggal Pengembalian --}}
<div id="editTanggalModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeEditTanggal()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-slate-900 text-[15px]">Edit Tanggal Pengembalian</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Permohonan masih bisa diedit selama menunggu review</p>
            </div>
            <button onclick="closeEditTanggal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-slate-500 text-xs"></i>
            </button>
        </div>

        <form id="editTanggalForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="space-y-4">
                <div>
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Tanggal Pengembalian Baru</label>
                    <input type="date" id="editTanggalInput" name="tanggal_pengembalian" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-[13px] font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditTanggal()"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-[12px] font-bold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        style="background-color: #2563eb;"
                        class="flex-1 py-2.5 rounded-xl hover:bg-blue-700 text-white text-[12px] font-bold transition-colors">
                        <i class="fas fa-save mr-1.5"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openEditTanggal(bookingId, currentDate, minDate) {
    const modal = document.getElementById('editTanggalModal');
    const form  = document.getElementById('editTanggalForm');
    const input = document.getElementById('editTanggalInput');

    form.action = '/booking/' + bookingId + '/update-tanggal';
    input.value = currentDate;
    input.min   = minDate;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEditTanggal() {
    const modal = document.getElementById('editTanggalModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openCancelModal(bookingId, namaItem) {
    document.getElementById('cancel-item-name').textContent = namaItem;
    document.getElementById('confirm-cancel-btn').onclick = function() {
        document.getElementById('cancel-form-' + bookingId).submit();
    };
    const modal = document.getElementById('cancelModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    // Animasi masuk
    setTimeout(() => {
        document.getElementById('cancelModalBox').classList.remove('scale-95', 'opacity-0');
        document.getElementById('cancelModalBox').classList.add('scale-100', 'opacity-100');
    }, 10);
}
function closeCancelModal() {
    const box = document.getElementById('cancelModalBox');
    box.classList.remove('scale-100', 'opacity-100');
    box.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        document.getElementById('cancelModal').classList.add('hidden');
        document.getElementById('cancelModal').classList.remove('flex');
    }, 200);
}
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Daftar Peminjaman')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pending</span>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $totalPending }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Menunggu persetujuan</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aktif</span>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-circle-check text-emerald-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $totalActive }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Sedang berjalan</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Hari Ini</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-day text-blue-500 text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-slate-900">{{ $bookings->total() }}</span>
            <p class="text-[11px] text-slate-400 mt-1">Total pengajuan</p>
        </div>

        <div class="bg-teal-600 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold text-teal-200 uppercase tracking-wider">Portal</span>
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-video text-white text-xs"></i>
                </div>
            </div>
            <span class="text-2xl font-bold text-white">UPT</span>
            <p class="text-[11px] text-teal-200 mt-1">Studio & Lab STAIMAS</p>
        </div>
    </div>

    {{-- Library Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tren Line Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 text-[13px] mb-4">📈 Tren Peminjaman (7 Hari Terakhir)</h3>
            <div class="h-60 relative w-full">
                <canvas id="chartTren"></canvas>
            </div>
        </div>

        {{-- Proporsi Doughnut Chart --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 text-[13px] mb-4">📊 Proporsi Layanan UPT</h3>
            <div class="h-60 relative w-full flex items-center justify-center">
                <canvas id="chartProporsi"></canvas>
            </div>
        </div>
    </div>

    {{-- Table & Filter Actions --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="font-semibold text-slate-900 text-[14px]">Semua Permohonan Peminjaman</h3>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Total: {{ $bookings->total() }} pengajuan ditemukan</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Form Filter --}}
                <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2">
                    <select name="tipe" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">Semua Kategori</option>
                        <option value="ruangan" {{ request('tipe') === 'ruangan' ? 'selected' : '' }}>Studio & Lab (Ruangan)</option>
                        <option value="peralatan" {{ request('tipe') === 'peralatan' ? 'selected' : '' }}>Peralatan</option>
                    </select>

                    <select name="periode" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        <option value="">Semua Periode</option>
                        <option value="hari" {{ request('periode') === 'hari' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="minggu" {{ request('periode') === 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="bulan" {{ request('periode') === 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                    </select>
                </form>

                {{-- Tombol Ekspor PDF Laporan --}}
                <a href="{{ route('admin.bookings.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-3.5 py-2 rounded-xl transition-colors shadow-sm">
                    <i class="fas fa-file-pdf"></i> Ekspor PDF Laporan
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Peminjam</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Waktu Pinjam</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">PJ Bertugas</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[13px] font-semibold text-slate-900">{{ $booking->nama_peminjam }}</p>
                                <p class="text-[11px] text-slate-400">{{ $booking->instansi_peminjam }}</p>
                                @if($booking->no_wa)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->no_wa) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-medium mt-0.5 hover:underline">
                                        <i class="fab fa-whatsapp"></i> {{ $booking->no_wa }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $firstItem = $booking->items->first(); @endphp
                                @if($booking->items->count() > 0)
                                    <div class="space-y-0.5">
                                        @foreach($booking->items as $itm)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[12px] font-semibold text-slate-800 truncate">{{ $itm->nama }}</span>
                                            @if($itm->pivot->jumlah > 1)
                                            <span class="text-[9px] font-bold text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded-md shrink-0">{{ $itm->pivot->jumlah }}x</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @if($firstItem)
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-100 uppercase">{{ $firstItem->kategori }}</span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-500 uppercase">{{ $firstItem->tipe }}</span>
                                    </div>
                                    @endif
                                @else
                                    {{-- Ruangan booking (from booking-ruangan component) --}}
                                    <p class="text-[12px] text-slate-400 italic">Lihat detail</p>
                                @endif
                                @if($booking->jumlah_kursi > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-0.5 mt-1"><i class="fas fa-chair text-[8px] mr-0.5"></i> {{ $booking->jumlah_kursi }} Kursi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($booking->jam_mulai)
                                    <p class="text-[13px] font-medium text-slate-700">{{ $booking->tanggal_peminjaman->format('d M Y') }}</p>
                                    <p class="text-[11px] font-bold text-teal-600 mt-0.5">
                                        <i class="far fa-clock mr-0.5"></i>
                                        {{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB
                                    </p>
                                    <span class="text-[10px] text-slate-400 font-semibold">(1 Hari / Jam Sesi)</span>
                                @else
                                    @php
                                        $diff = $booking->tanggal_peminjaman->diffInDays($booking->tanggal_pengembalian);
                                        $days = $diff < 1 ? 1 : $diff + 1;
                                    @endphp
                                    <p class="text-[13px] font-medium text-slate-700">
                                        {{ $booking->tanggal_peminjaman->format('d M Y') }}
                                        <span class="text-slate-400">s/d</span>
                                        {{ $booking->tanggal_pengembalian->format('d M Y') }}
                                    </p>
                                    <span class="inline-flex items-center gap-1 text-[10px] text-teal-600 font-bold mt-1 bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">
                                        <i class="fas fa-calendar-day text-[9px]"></i> {{ $days }} Hari Peminjaman
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($booking->penanggungJawab)
                                    <p class="text-[13px] font-medium text-slate-700">{{ $booking->penanggungJawab->name }}</p>
                                    <p class="text-[11px] text-slate-400">PJ UPT</p>
                                @else
                                    <span class="text-[11px] text-slate-300 italic">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ asset('storage/' . $booking->bukti_peminjam) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 text-[12px] text-teal-600 font-medium hover:text-teal-800 hover:underline">
                                    <i class="fas fa-file-image text-xs"></i> Lihat Bukti
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    @if($booking->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
                                    @elseif($booking->status === 'disetujui') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($booking->status === 'ditolak') bg-red-50 text-red-700 border border-red-200
                                    @else bg-slate-100 text-slate-500 border border-slate-200
                                    @endif">
                                    @if($booking->status === 'pending') <span class="w-1.5 h-1.5 bg-amber-400 rounded-full mr-1.5 animate-pulse"></span>
                                    @elseif($booking->status === 'disetujui') <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full mr-1.5"></span>
                                    @elseif($booking->status === 'ditolak') <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                    @else <span class="w-1.5 h-1.5 bg-slate-400 rounded-full mr-1.5"></span>
                                    @endif
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                    @if($booking->status === 'pending')
                                        <button 
                                            data-id="{{ $booking->id }}"
                                            data-nama="{{ $booking->nama_peminjam }}"
                                            data-instansi="{{ $booking->instansi_peminjam }}"
                                            data-item="{{ $booking->items->pluck('nama')->implode(', ') }}"
                                            data-waktu="{{ $booking->tanggal_peminjaman->format('d M Y') }} @if($booking->jam_mulai)({{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB)@endif"
                                            data-bukti="{{ asset('storage/' . $booking->bukti_peminjam) }}"
                                            data-wa="{{ $booking->no_wa }}"
                                            data-kursi="{{ $booking->jumlah_kursi }}"
                                            data-catatan="{{ $booking->catatan }}"
                                            data-status="{{ $booking->status }}"
                                            onclick="openReviewModal(this)"
                                            class="px-2.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold rounded-lg transition-colors shrink-0">
                                            Tinjau
                                        </button>
                                    @elseif($booking->status === 'disetujui')
                                        <form action="{{ route('admin.bookings.status', $booking->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="selesai">
                                            <button class="px-2.5 py-1.5 bg-slate-700 hover:bg-slate-900 text-white text-[11px] font-bold rounded-lg transition-colors shrink-0">Selesai</button>
                                        </form>
                                    @endif

                                    <button 
                                        data-id="{{ $booking->id }}"
                                        data-nama="{{ $booking->nama_peminjam }}"
                                        data-instansi="{{ $booking->instansi_peminjam }}"
                                        data-item="{{ $booking->items->pluck('nama')->implode(', ') }}"
                                        data-waktu="{{ $booking->tanggal_peminjaman->format('d M Y') }} @if($booking->jam_mulai)({{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB)@endif"
                                        data-bukti="{{ asset('storage/' . $booking->bukti_peminjam) }}"
                                        data-wa="{{ $booking->no_wa }}"
                                        data-kursi="{{ $booking->jumlah_kursi }}"
                                        data-catatan="{{ $booking->catatan }}"
                                        data-status="{{ $booking->status }}"
                                        onclick="openReviewModal(this)"
                                        class="px-2.5 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-600 text-[11px] font-bold rounded-lg transition-colors flex items-center gap-1">
                                        <i class="far fa-eye text-xs"></i> Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-inbox text-slate-300 text-xl"></i>
                                </div>
                                <p class="text-[13px] font-semibold text-slate-400">Belum ada permohonan masuk</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ===== MODAL TINJAU & PROSES BOOKING ===== --}}
<div id="modal-review" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-[14px]" id="review-title">Tinjau Pengajuan Peminjaman</h3>
            <button onclick="document.getElementById('modal-review').classList.add('hidden')"
                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
            {{-- Biodata Singkat --}}
            <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="space-y-1">
                    <span class="text-slate-400 block font-semibold uppercase text-[9px]">Peminjam</span>
                    <strong id="review-nama" class="text-slate-800 text-[12px] block"></strong>
                    <span id="review-instansi" class="block text-slate-500 font-medium"></span>
                    
                    {{-- No WA --}}
                    <div id="review-wa-container" class="mt-2 pt-2 border-t border-slate-200/60">
                        <span class="text-slate-400 block font-semibold uppercase text-[9px]">WhatsApp</span>
                        <a id="review-wa-link" href="" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline mt-0.5">
                            <i class="fab fa-whatsapp"></i> <span id="review-wa-text"></span>
                        </a>
                    </div>
                </div>
                <div class="space-y-1">
                    <span class="text-slate-400 block font-semibold uppercase text-[9px]">Item & Jadwal</span>
                    <strong id="review-item" class="text-slate-800 text-[12px] block"></strong>
                    <span id="review-waktu" class="block text-slate-500 font-medium"></span>

                    {{-- Jumlah Kursi --}}
                    <div id="review-kursi-container" class="mt-2 pt-2 border-t border-slate-200/60 hidden">
                        <span class="text-slate-400 block font-semibold uppercase text-[9px]">Permintaan Kursi</span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 mt-1">
                            <i class="fas fa-chair text-[8px]"></i> <span id="review-kursi-text">0</span> Kursi
                        </span>
                    </div>
                </div>
            </div>

            {{-- Alasan Peminjaman --}}
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Alasan Peminjaman</span>
                <p id="review-alasan" class="text-[12px] text-slate-600 bg-slate-50 border border-slate-100 rounded-xl p-3 leading-relaxed"></p>
            </div>

            {{-- Bukti Upload KTM/KTP --}}
            <div class="space-y-1.5">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Berkas Bukti Identitas (KTM/KTP)</span>
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-100 flex items-center justify-center aspect-[16/9] shadow-inner relative group">
                    <img id="review-bukti-img" src="" alt="KTM/KTP" class="max-h-full max-w-full object-contain">
                    <a id="review-bukti-fullscreen" href="" target="_blank" class="absolute bottom-2 right-2 bg-slate-900/65 hover:bg-slate-900/80 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                        <i class="fas fa-expand"></i> Perbesar Bukti
                    </a>
                </div>
            </div>

            {{-- Form Catatan Persetujuan --}}
            <form id="review-form" action="" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="status" id="review-status-input" value="disetujui">
                
                <div class="space-y-1.5" id="review-catatan-container">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Tulis Catatan / Alasan (Opsional)</label>
                    <textarea name="catatan" id="review-catatan" rows="2" placeholder="Catatan pengambilan alat, alasan penolakan, dll..."
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2" id="review-actions-container">
                    <button type="button" onclick="submitReview('ditolak')" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl text-[13px] transition-colors">
                        ❌ Tolak Peminjaman
                    </button>
                    <button type="button" onclick="submitReview('disetujui')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-[13px] transition-colors">
                        ✅ Setujui Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openReviewModal(btn) {
    const id = btn.getAttribute('data-id');
    const nama = btn.getAttribute('data-nama');
    const instansi = btn.getAttribute('data-instansi');
    const item = btn.getAttribute('data-item');
    const waktu = btn.getAttribute('data-waktu');
    const bukti = btn.getAttribute('data-bukti');
    const wa = btn.getAttribute('data-wa');
    const kursi = parseInt(btn.getAttribute('data-kursi') || '0');
    const catatan = btn.getAttribute('data-catatan') || 'Tidak ada catatan/alasan spesifik yang ditulis.';
    const status = btn.getAttribute('data-status');

    // Bind data dasar
    document.getElementById('review-nama').innerText = nama;
    document.getElementById('review-instansi').innerText = instansi;
    document.getElementById('review-item').innerText = item;
    document.getElementById('review-waktu').innerText = waktu;
    document.getElementById('review-bukti-img').src = bukti;
    document.getElementById('review-bukti-fullscreen').href = bukti;
    document.getElementById('review-alasan').innerText = catatan;

    // Bind No WA
    if (wa && wa.trim() !== '') {
        document.getElementById('review-wa-container').classList.remove('hidden');
        document.getElementById('review-wa-text').innerText = wa;
        const cleanWa = wa.replace(/\D/g, '');
        document.getElementById('review-wa-link').href = `https://wa.me/${cleanWa}`;
    } else {
        document.getElementById('review-wa-container').classList.add('hidden');
    }

    // Bind Kursi (Bioskop Style)
    const kursiContainer = document.getElementById('review-kursi-container');
    if (kursi > 0) {
        kursiContainer.classList.remove('hidden');
        document.getElementById('review-kursi-text').innerText = kursi;
    } else {
        kursiContainer.classList.add('hidden');
    }

    // Kontrol Visibilitas Form & Tombol Aksi berdasarkan Status
    const title = document.getElementById('review-title');
    const formContainer = document.getElementById('review-form');
    const catatanContainer = document.getElementById('review-catatan-container');
    const actionsContainer = document.getElementById('review-actions-container');

    if (status === 'pending') {
        title.innerText = 'Tinjau Pengajuan Peminjaman';
        formContainer.action = `/admin/bookings/${id}/status`;
        catatanContainer.classList.remove('hidden');
        actionsContainer.classList.remove('hidden');
    } else {
        title.innerText = `Detail Peminjaman (${status.toUpperCase()})`;
        formContainer.action = '#';
        catatanContainer.classList.add('hidden');
        actionsContainer.classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('modal-review').classList.remove('hidden');
}

function submitReview(status) {
    document.getElementById('review-status-input').value = status;
    document.getElementById('review-form').submit();
}

// Inisialisasi Chart.js
document.addEventListener("DOMContentLoaded", function() {
    // 1. Chart Tren Peminjaman
    const ctxTren = document.getElementById('chartTren').getContext('2d');
    new Chart(ctxTren, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: {!! json_encode($chartData) !!},
                borderColor: '#0d9488', // teal-600
                backgroundColor: 'rgba(13, 148, 136, 0.1)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#0d9488',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#94a3b8'
                    },
                    grid: {
                        borderDash: [5, 5],
                        color: '#f1f5f9'
                    }
                },
                x: {
                    ticks: {
                        color: '#94a3b8'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 2. Chart Proporsi Layanan
    const ctxProporsi = document.getElementById('chartProporsi').getContext('2d');
    new Chart(ctxProporsi, {
        type: 'doughnut',
        data: {
            labels: ['Ruangan (Studio/Lab)', 'Peralatan'],
            datasets: [{
                data: [{{ $countRuangan }}, {{ $countPeralatan }}],
                backgroundColor: [
                    '#0d9488', // teal-600
                    '#6366f1'  // indigo-500
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11,
                            family: 'Inter'
                        },
                        color: '#64748b'
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
@endsection

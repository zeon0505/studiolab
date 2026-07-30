@extends('layouts.admin')
@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h2 class="text-[15px] font-bold text-slate-900">Riwayat Peminjaman Lampau</h2>
        <p class="text-[12px] text-slate-400 mt-0.5">Daftar semua transaksi peminjaman yang sudah selesai atau ditolak.</p>
    </div>

    {{-- Filter & Pencarian Actions --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Form Pencarian & Filter --}}
            <form action="{{ route('admin.history') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                {{-- Input Pencarian --}}
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fas fa-search text-[11px]"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, instansi, atau item..."
                        class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600 focus:outline-none focus:ring-1 focus:ring-teal-500">
                </div>

                {{-- Filter Kategori Tipe --}}
                <select name="tipe" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-600 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="">Semua Tipe</option>
                    <option value="ruangan" {{ request('tipe') === 'ruangan' ? 'selected' : '' }}>Ruangan</option>
                    <option value="peralatan" {{ request('tipe') === 'peralatan' ? 'selected' : '' }}>Peralatan</option>
                </select>

                @if(request()->filled('search') || request()->filled('tipe'))
                    <a href="{{ route('admin.history') }}" class="text-[11px] text-red-500 hover:text-red-700 font-bold">Reset</a>
                @endif
            </form>
        </div>

        {{-- Tabel Riwayat --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Peminjam</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Waktu Pinjam</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">PJ Bertugas</th>
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
                                <p class="text-[13px] font-semibold text-slate-800">{{ $booking->item->nama }}</p>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-100 uppercase">{{ $booking->item->kategori }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-500 uppercase">{{ $booking->item->tipe }}</span>
                                    @if($booking->jumlah_kursi > 0)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><i class="fas fa-chair text-[8px] mr-0.5"></i> {{ $booking->jumlah_kursi }} Kursi</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[13px] font-medium text-slate-700">{{ $booking->tanggal_peminjaman->format('d M Y') }}</p>
                                @if($booking->jam_mulai)
                                    <p class="text-[12px] font-bold text-teal-600 mt-0.5">
                                        <i class="far fa-clock mr-0.5"></i>
                                        {{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} – {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB
                                    </p>
                                @else
                                    <p class="text-[11px] text-slate-400 mt-0.5">s/d {{ $booking->tanggal_pengembalian->format('d M Y') }}</p>
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
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    @if($booking->status === 'disetujui') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($booking->status === 'ditolak') bg-red-50 text-red-700 border border-red-200
                                    @else bg-slate-100 text-slate-500 border border-slate-200
                                    @endif">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                        @if($booking->status === 'disetujui') bg-emerald-400
                                        @elseif($booking->status === 'ditolak') bg-red-400
                                        @else bg-slate-400
                                        @endif"></span>
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    data-id="{{ $booking->id }}"
                                    data-nama="{{ $booking->nama_peminjam }}"
                                    data-instansi="{{ $booking->instansi_peminjam }}"
                                    data-item="{{ $booking->item->nama }}"
                                    data-waktu="{{ $booking->tanggal_peminjaman->format('d M Y') }} @if($booking->jam_mulai)({{ \Carbon\Carbon::parse($booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->jam_selesai)->format('H:i') }} WIB)@endif"
                                    data-bukti="{{ asset('storage/' . $booking->bukti_peminjam) }}"
                                    data-wa="{{ $booking->no_wa }}"
                                    data-kursi="{{ $booking->jumlah_kursi }}"
                                    data-catatan="{{ $booking->catatan }}"
                                    data-status="{{ $booking->status }}"
                                    onclick="openReviewModal(this)"
                                    class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-600 text-[11px] font-bold rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                                    <i class="far fa-eye text-xs"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-history text-slate-300 text-xl"></i>
                                </div>
                                <p class="text-[13px] font-semibold text-slate-400">Belum ada riwayat peminjaman lampau</p>
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

{{-- ===== MODAL DETAIL RIWAYAT ===== --}}
<div id="modal-review" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-[14px]" id="review-title">Detail Peminjaman</h3>
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

    // Bind Kursi
    const kursiContainer = document.getElementById('review-kursi-container');
    if (kursi > 0) {
        kursiContainer.classList.remove('hidden');
        document.getElementById('review-kursi-text').innerText = kursi;
    } else {
        kursiContainer.classList.add('hidden');
    }

    // Bind Status
    document.getElementById('review-title').innerText = `Detail Peminjaman (${status.toUpperCase()})`;
    
    // Show modal
    document.getElementById('modal-review').classList.remove('hidden');
}
</script>
@endsection

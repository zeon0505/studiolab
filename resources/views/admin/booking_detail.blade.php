@extends('layouts.admin')
@section('title', 'Detail Peminjaman #BKG-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors shrink-0">
            <i class="fas fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-base font-black text-slate-900">Verifikasi Scan QR</h1>
            <p class="text-[11px] text-slate-500 mt-0.5">Detail data peminjaman resmi UPT STAIMAS</p>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs p-3.5 rounded-2xl flex items-center gap-2 mb-6 shadow-sm">
            <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Main Detail Card --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        @php
            $isRuangan = $booking->items->where('tipe', 'ruangan')->count() > 0;
            $statusColors = [
                'pending'   => 'bg-amber-50 text-amber-800 border-amber-200',
                'disetujui' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'ditolak'   => 'bg-red-50 text-red-800 border-red-200',
                'selesai'   => 'bg-slate-50 text-slate-600 border-slate-200',
            ];
            $color = $statusColors[$booking->status] ?? $statusColors['pending'];
        @endphp

        {{-- Badge Status --}}
        <div class="p-6 pb-4 border-b border-slate-50 flex items-center justify-between">
            <span class="font-mono text-xs font-bold text-slate-400">#BKG-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $color }}">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        {{-- Details --}}
        <div class="p-6 divide-y divide-slate-50 space-y-0">
            {{-- Items (multi-item) --}}
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-10 h-10 rounded-2xl {{ $isRuangan ? 'bg-blue-50 text-blue-600' : 'bg-teal-50 text-teal-600' }} flex items-center justify-center shrink-0">
                    <i class="{{ $isRuangan ? 'fas fa-door-open' : 'fas fa-shopping-basket' }} text-base"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Item Dipinjam</p>
                    @if($booking->items->count() > 0)
                        <div class="mt-1 space-y-1.5">
                            @foreach($booking->items as $itm)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900">{{ $itm->nama }}</span>
                                <div class="flex items-center gap-1.5 ml-2 shrink-0">
                                    @if($itm->pivot->jumlah > 1)
                                    <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-md">{{ $itm->pivot->jumlah }}x</span>
                                    @endif
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-slate-100 text-slate-500 uppercase">{{ $itm->tipe }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-teal-50 text-teal-700 uppercase">{{ $itm->kategori }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 mt-0.5 italic">Lihat detail peminjaman ruangan</p>
                    @endif
                </div>
            </div>

            {{-- Jadwal --}}
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-calendar-alt text-base"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Waktu Peminjaman</p>
                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $booking->tanggal_peminjaman->translatedFormat('d F Y') }}</p>
                    @if($booking->jam_mulai)
                        <p class="text-xs font-bold text-teal-700 mt-0.5">{{ substr($booking->jam_mulai, 0, 5) }} – {{ substr($booking->jam_selesai, 0, 5) }} WIB</p>
                    @elseif($booking->tanggal_pengembalian)
                        <p class="text-xs text-slate-500 mt-0.5">s/d {{ $booking->tanggal_pengembalian->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
            </div>

            {{-- User --}}
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-user text-base"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Peminjam</p>
                    <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $booking->nama_peminjam }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $booking->instansi_peminjam }}</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $booking->no_wa) }}" target="_blank"
                       class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg border border-green-200 mt-2 hover:bg-green-100 transition-colors">
                        <i class="fab fa-whatsapp"></i> Hubungi via WA
                    </a>
                </div>
            </div>

            {{-- KTM --}}
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-id-card text-base"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Identitas KTM / KTP</p>
                    @if($booking->bukti_peminjam)
                        <div class="mt-2 rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 max-w-[200px]">
                            <a href="{{ asset('storage/' . $booking->bukti_peminjam) }}" target="_blank">
                                <img src="{{ asset('storage/' . $booking->bukti_peminjam) }}" alt="KTM" class="w-full h-auto max-h-40 object-cover hover:opacity-90 transition-opacity">
                            </a>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-1 italic">Klik gambar untuk memperbesar</p>
                    @else
                        <p class="text-xs text-red-500 mt-0.5">Tidak ada foto identitas.</p>
                    @endif
                </div>
            </div>

            {{-- PJ & Catatan --}}
            @if($booking->catatan)
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-comment-alt text-base"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Alasan / Catatan</p>
                    <p class="text-xs text-slate-700 mt-1 bg-slate-50 border border-slate-100 p-2.5 rounded-xl leading-relaxed">{{ $booking->catatan }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Admin Actions Form --}}
    @if(in_array($booking->status, ['pending', 'disetujui']))
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-black text-slate-900 text-sm mb-4"><i class="fas fa-sliders-h text-teal-600 mr-1.5"></i> Perbarui Status Peminjaman</h3>
        <form action="{{ route('admin.bookings.status', $booking->id) }}" method="POST" class="space-y-4" id="status-form">
            @csrf
            {{-- Hidden input yang benar-benar disubmit --}}
            <input type="hidden" name="status" id="selected-status" value="">

            <div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-3">Pilih Status Baru</label>
                <div class="grid grid-cols-3 gap-3">
                    @if($booking->status === 'pending')
                    <button type="button" onclick="selectStatus('disetujui', this)"
                            data-value="disetujui"
                            class="status-btn border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-emerald-300 hover:bg-emerald-50 transition-all">
                        <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                        <span class="text-[11px] font-bold text-slate-700">Setujui</span>
                    </button>
                    <button type="button" onclick="selectStatus('ditolak', this)"
                            data-value="ditolak"
                            class="status-btn border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all">
                        <i class="fas fa-times-circle text-red-500 text-xl"></i>
                        <span class="text-[11px] font-bold text-slate-700">Tolak</span>
                    </button>
                    @endif
                    <button type="button" onclick="selectStatus('selesai', this)"
                            data-value="selesai"
                            class="status-btn border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-slate-400 hover:bg-slate-50 transition-all">
                        <i class="fas fa-flag-checkered text-slate-500 text-xl"></i>
                        <span class="text-[11px] font-bold text-slate-700">Selesai</span>
                    </button>
                </div>
                <p id="status-error" class="hidden text-xs text-red-500 mt-2"><i class="fas fa-exclamation-circle mr-1"></i>Pilih salah satu status terlebih dahulu.</p>
            </div>

            <div id="catatan-wrapper" style="display:none; overflow:hidden; transition: all 0.3s ease;">
                <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
                    <label class="text-[10px] font-black text-red-500 uppercase tracking-wider block mb-1.5">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="catatan" id="catatan-input" rows="3"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-red-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                              placeholder="Tuliskan alasan penolakan permohonan ini..."></textarea>
                    <p id="catatan-error" class="hidden text-xs text-red-600 mt-1.5 font-semibold"><i class="fas fa-exclamation-circle mr-1"></i>Alasan penolakan wajib diisi.</p>
                </div>
            </div>

            <button type="button" onclick="submitStatus()"
                    class="w-full py-3 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm transition-colors shadow-md flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane text-xs"></i> Simpan & Kirim WA Notifikasi
            </button>
        </form>

        <script>
        const statusColors = {
            disetujui: { border: 'border-emerald-500', bg: 'bg-emerald-50', text: 'text-emerald-700' },
            ditolak:   { border: 'border-red-500',     bg: 'bg-red-50',     text: 'text-red-700'     },
            selesai:   { border: 'border-slate-600',   bg: 'bg-slate-100',  text: 'text-slate-800'   },
        };

        function selectStatus(value, btn) {
            // Reset all buttons
            document.querySelectorAll('.status-btn').forEach(b => {
                b.className = 'status-btn border-2 border-slate-100 rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-slate-300 hover:bg-slate-50 transition-all';
            });

            // Highlight selected
            const c = statusColors[value] || {};
            btn.className = `status-btn border-2 ${c.border} ${c.bg} rounded-2xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer transition-all scale-[1.03] shadow-md`;

            // Set hidden input
            document.getElementById('selected-status').value = value;
            document.getElementById('status-error').classList.add('hidden');

            // Tampilkan / sembunyikan kotak alasan penolakan
            const wrapper = document.getElementById('catatan-wrapper');
            if (value === 'ditolak') {
                wrapper.style.display = 'block';
                setTimeout(() => { wrapper.style.opacity = '1'; wrapper.style.transform = 'translateY(0)'; }, 10);
                document.getElementById('catatan-input').focus();
            } else {
                wrapper.style.display = 'none';
                document.getElementById('catatan-input').value = '';
                document.getElementById('catatan-error').classList.add('hidden');
            }
        }

        function submitStatus() {
            const status = document.getElementById('selected-status').value;
            const catatan = document.getElementById('catatan-input').value.trim();

            // Validasi: harus pilih status
            if (!status) {
                document.getElementById('status-error').classList.remove('hidden');
                return;
            }

            // Validasi: jika ditolak, catatan wajib
            if (status === 'ditolak' && !catatan) {
                document.getElementById('catatan-error').classList.remove('hidden');
                return;
            }

            document.getElementById('catatan-error').classList.add('hidden');
            document.getElementById('status-form').submit();
        }
        </script>
    </div>
    @endif

</div>
@endsection

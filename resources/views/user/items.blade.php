@extends('layouts.user')
@section('title', $tipe === 'ruangan' ? 'Kelola Ruangan' : 'Kelola Peralatan')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[15px] font-bold text-slate-900">
                {{ $tipe === 'ruangan' ? 'Manajemen Ruangan UPT (Studio & Lab)' : 'Manajemen Peralatan UPT' }}
            </h2>
            <p class="text-[12px] text-slate-400 mt-0.5">
                {{ $tipe === 'ruangan' ? 'Kelola data ruangan studio penyiaran, podcast, dan laboratorium terpadu.' : 'Kelola inventarisasi barang, kamera, mixer, proyektor, dan perlengkapan lainnya.' }}
            </p>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-bold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
            <i class="fas fa-plus text-xs"></i> Tambah {{ $tipe === 'ruangan' ? 'Ruangan' : 'Peralatan' }}
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Item</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</th>
                        @if($tipe === 'peralatan')
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stok</th>
                        @else
                            <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kapasitas Kursi</th>
                        @endif
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-[13px] font-semibold text-slate-900">{{ $item->nama }}</p>
                                <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $item->deskripsi }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    {{ $item->kategori === 'studio' ? 'bg-violet-50 text-violet-700 border border-violet-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            @if($tipe === 'peralatan')
                                <td class="px-6 py-4">
                                    <span class="text-[13px] font-bold text-slate-800">{{ $item->stok }}</span>
                                    <span class="text-[11px] text-slate-400 ml-1">unit</span>
                                </td>
                            @else
                                <td class="px-6 py-4">
                                    <span class="text-[13px] font-bold text-slate-800">{{ $item->kapasitas_kursi }}</span>
                                    <span class="text-[11px] text-slate-400 ml-1">kursi</span>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    @if($item->status === 'tersedia') bg-slate-100 text-slate-700 border border-slate-200
                                    @elseif($item->status === 'dipinjam') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @else bg-red-50 text-red-700 border border-red-200
                                    @endif">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                        @if($item->status === 'tersedia') bg-slate-400
                                        @elseif($item->status === 'dipinjam') bg-emerald-500 animate-pulse
                                        @else bg-red-500
                                        @endif"></span>
                                    {{ $item->status === 'tersedia' ? 'Belum Dibooking' : ($item->status === 'dipinjam' ? 'Sudah Dibooking' : $item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <button 
                                        data-id="{{ $item->id }}"
                                        data-nama="{{ $item->nama }}"
                                        data-kategori="{{ $item->kategori }}"
                                        data-tipe="{{ $item->tipe }}"
                                        data-stok="{{ $item->stok }}"
                                        data-kapasitas="{{ $item->kapasitas_kursi }}"
                                        data-status="{{ $item->status }}"
                                        data-deskripsi="{{ $item->deskripsi }}"
                                        onclick="initEditItem(this)"
                                        class="text-[12px] font-semibold text-teal-600 hover:text-teal-800 transition-colors">
                                        Edit
                                    </button>
                                    <form id="delete-item-{{ $item->id }}" action="{{ route('user.items.destroy', $item->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="showConfirm('Hapus item &quot;{{ addslashes($item->nama) }}&quot;? Data yang sudah dihapus tidak dapat dikembalikan.', () => document.getElementById('delete-item-{{ $item->id }}').submit(), { title: 'Hapus Item', okLabel: 'Ya, Hapus' })"
                                            class="text-[12px] font-semibold text-red-400 hover:text-red-700 transition-colors">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-box-open text-slate-300 text-xl"></i>
                                </div>
                                <p class="text-[13px] font-semibold text-slate-400">Belum ada data inventaris</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ===== MODAL TAMBAH ITEM ===== --}}
<div id="modal-add" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-[14px]">Tambah {{ $tipe === 'ruangan' ? 'Ruangan' : 'Peralatan' }} Baru</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form action="{{ route('user.items.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="tipe" value="{{ $tipe }}">
            
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Nama {{ $tipe === 'ruangan' ? 'Ruangan' : 'Peralatan' }}</label>
                <input type="text" name="nama" required placeholder="Contoh: {{ $tipe === 'ruangan' ? 'Ruang Studio Podcast' : 'Kamera DSLR Canon 200D' }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Kategori Wilayah</label>
                    <select name="kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="studio">Studio</option>
                        <option value="laboratorium">Laboratorium</option>
                    </select>
                </div>
                
                @if($tipe === 'peralatan')
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jumlah Stok</label>
                        <input type="number" name="stok" value="1" min="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                @else
                    <input type="hidden" name="stok" value="1">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Kapasitas Kursi</label>
                        <input type="number" name="kapasitas_kursi" value="0" min="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500" placeholder="Kapasitas kursi">
                    </div>
                @endif
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Foto (Opsional)</label>
                <input type="file" name="gambar" class="w-full text-[12px] py-1">
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Deskripsi Detail</label>
                <textarea name="deskripsi" rows="3" placeholder="Deskripsi mengenai spesifikasi atau fasilitas..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
            </div>
            
            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-[13px] transition-colors">
                Simpan Data
            </button>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ITEM ===== --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-[14px]">Edit Data</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="tipe" value="{{ $tipe }}">

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Nama Item</label>
                <input type="text" name="nama" id="edit-nama" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Kategori</label>
                    <select name="kategori" id="edit-kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="studio">Studio</option>
                        <option value="laboratorium">Laboratorium</option>
                    </select>
                </div>
                
                @if($tipe === 'peralatan')
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Jumlah Stok</label>
                        <input type="number" name="stok" id="edit-stok" min="1" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                @else
                    <input type="hidden" name="stok" value="1">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Kapasitas Kursi</label>
                        <input type="number" name="kapasitas_kursi" id="edit-kapasitas-kursi" min="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                @endif
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Status Ketersediaan</label>
                <select name="status" id="edit-status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="tersedia">Tersedia</option>
                    <option value="dipinjam">Dipinjam</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Foto (Opsional)</label>
                <input type="file" name="gambar" class="w-full text-[12px] py-1">
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Deskripsi</label>
                <textarea name="deskripsi" id="edit-deskripsi" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500"></textarea>
            </div>
            
            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-[13px] transition-colors">
                Perbarui Data
            </button>
        </form>
    </div>
</div>

<script>
function initEditItem(btn) {
    const id = btn.getAttribute('data-id');
    const nama = btn.getAttribute('data-nama');
    const kategori = btn.getAttribute('data-kategori');
    const tipe = btn.getAttribute('data-tipe');
    const stok = btn.getAttribute('data-stok');
    const kapasitas = btn.getAttribute('data-kapasitas');
    const status = btn.getAttribute('data-status');
    const deskripsi = btn.getAttribute('data-deskripsi');

    document.getElementById('edit-form').action = `/dashboard/items/${id}/update`;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-kategori').value = kategori;
    
    const stokInput = document.getElementById('edit-stok');
    if (stokInput) stokInput.value = stok;
    
    const kapasitasInput = document.getElementById('edit-kapasitas-kursi');
    if (kapasitasInput) kapasitasInput.value = kapasitas || 0;
    
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-deskripsi').value = deskripsi || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection

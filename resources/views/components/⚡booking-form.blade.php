<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Item;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\DailyAssignment;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithFileUploads;

    // Form inputs
    public ?string $nama_peminjam = null;
    public ?string $instansi_peminjam = null;
    public mixed $bukti_peminjam = null; // KTM/KTP upload
    public ?string $tanggal_peminjaman = null;
    public ?string $tanggal_pengembalian = null;
    
    // Waktu booking jam (khusus ruangan)
    public ?string $jam_mulai = null;
    public ?string $jam_selesai = null;
    
    public ?string $no_wa = null;
    public ?string $catatan = null;

    // Kapasitas Kursi (khusus Ruangan)
    public int $jumlah_kursi = 1;
    public int $maxKapasitasKursi = 0;

    // Menu Pemisah Mutlak
    public string $mode = 'ruangan'; // peralatan / ruangan
    
    public string $kategori = 'studio'; // studio / laboratorium
    public string $selected_item_id = ''; // Pilihan item

    // PJ bertugas harian
    public string $currentPjName = '';
    public ?int $currentPjId = null;

    // Options list
    public $availableItems = [];

    public function mount()
    {
        $this->tanggal_peminjaman = Carbon::today()->format('Y-m-d');
        $this->tanggal_pengembalian = Carbon::today()->format('Y-m-d');
        $this->jam_mulai = '09:00';
        $this->jam_selesai = '12:00';
        
        $this->loadItems();
        $this->checkPjAssignment();
    }

    public function updatedMode()
    {
        $this->loadItems();
    }

    public function updatedKategori()
    {
        $this->loadItems();
    }

    public function updatedSelectedItemId(mixed $value)
    {
        if ($this->mode === 'ruangan' && !empty($value)) {
            $item = Item::find($value);
            if ($item) {
                $this->maxKapasitasKursi = $item->kapasitas_kursi;
                $this->jumlah_kursi = 1;
            }
        } else {
            $this->maxKapasitasKursi = 0;
            $this->jumlah_kursi = 0;
        }
    }

    public function updatedTanggalPeminjaman()
    {
        if ($this->mode === 'ruangan') {
            $this->tanggal_pengembalian = $this->tanggal_peminjaman; // Peminjaman ruang di hari yang sama
        }
        $this->checkPjAssignment();
    }

    public function loadItems()
    {
        $this->availableItems = Item::where('kategori', $this->kategori)
            ->where('tipe', $this->mode)
            ->where('status', 'tersedia')
            ->get();
        $this->selected_item_id = '';
    }

    public function checkPjAssignment()
    {
        if (empty($this->tanggal_peminjaman)) {
            $this->currentPjName = 'Tidak ada tanggal dipilih';
            $this->currentPjId = null;
            return;
        }

        $dayIndex = Carbon::parse($this->tanggal_peminjaman)->dayOfWeek; 
        // 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
        
        $daysMap = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            0 => 'minggu',
        ];

        $dayName = $daysMap[$dayIndex] ?? 'senin';

        $assignment = DailyAssignment::with('user')->where('hari', $dayName)->first();

        if ($assignment) {
            $this->currentPjName = $assignment->user->name;
            $this->currentPjId = $assignment->user->id;
        } else {
            $this->currentPjName = 'Belum diatur oleh admin';
            $this->currentPjId = null;
        }
    }

    public function save()
    {
        // Peminjam wajib login
        if (!Auth::check()) {
            session()->flash('error', 'Anda harus masuk/login terlebih dahulu untuk mengajukan peminjaman.');
            return;
        }

        $rules = [
            'nama_peminjam' => 'required|string|max:255',
            'instansi_peminjam' => 'required|string|max:255',
            'no_wa' => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'bukti_peminjam' => 'required|image|max:2048', // Max 2MB image
            'tanggal_peminjaman' => 'required|date|after_or_equal:today',
            'selected_item_id' => 'required|exists:items,id',
            'catatan' => 'nullable|string|max:1000',
        ];

        if ($this->mode === 'ruangan') {
            $rules['jam_mulai'] = 'required';
            $rules['jam_selesai'] = 'required';
            $rules['tanggal_pengembalian'] = 'required|date|same:tanggal_peminjaman';
            $rules['jumlah_kursi'] = 'required|integer|min:1|max:' . ($this->maxKapasitasKursi ?: 999);
        } else {
            $rules['tanggal_pengembalian'] = 'required|date|after_or_equal:tanggal_peminjaman';
        }

        $this->validate($rules, [
            'selected_item_id.required' => 'Silakan pilih alat atau ruangan yang ingin dipinjam.',
            'bukti_peminjam.required' => 'Silakan unggah foto KTM/KTP sebagai bukti identitas.',
            'tanggal_pengembalian.after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
            'tanggal_pengembalian.same' => 'Peminjaman ruangan harus dikembalikan di hari yang sama.',
            'jumlah_kursi.max' => 'Jumlah kursi yang diminta melebihi kapasitas ruangan (' . $this->maxKapasitasKursi . ' kursi).',
            'jumlah_kursi.min' => 'Jumlah kursi minimal adalah 1.',
        ]);

        // Store file upload
        $path = $this->bukti_peminjam->store('proofs', 'public');

        // Create booking
        $booking = Booking::create([
            'item_id' => $this->selected_item_id,
            'user_id' => Auth::id(),
            'penanggung_jawab_id' => $this->currentPjId,
            'nama_peminjam' => $this->nama_peminjam,
            'instansi_peminjam' => $this->instansi_peminjam,
            'no_wa' => $this->no_wa,
            'bukti_peminjam' => $path,
            'tanggal_peminjaman' => $this->tanggal_peminjaman,
            'tanggal_pengembalian' => $this->tanggal_pengembalian,
            'jam_mulai' => $this->mode === 'ruangan' ? $this->jam_mulai : null,
            'jam_selesai' => $this->mode === 'ruangan' ? $this->jam_selesai : null,
            'jumlah_kursi' => $this->mode === 'ruangan' ? $this->jumlah_kursi : 0,
            'status' => 'pending',
            'catatan' => $this->catatan,
        ]);

        // Kirim notifikasi WhatsApp ke PJ bertugas
        $booking->load('items', 'penanggungJawab');
        $selectedItem = Item::find($this->selected_item_id);

        // Create booking_items record
        if ($selectedItem) {
            BookingItem::create([
                'booking_id' => $booking->id,
                'item_id'    => $selectedItem->id,
                'jumlah'     => 1,
            ]);
        }

        $whatsapp = app(WhatsAppService::class);
        $whatsapp->notifyPj([
            'booking_id' => $booking->id,
            'pj_name' => $booking->penanggungJawab?->name ?? 'PJ Bertugas',
            'pj_no_wa' => $booking->penanggungJawab?->no_wa ?? '',
            'nama_peminjam' => $this->nama_peminjam,
            'instansi_peminjam' => $this->instansi_peminjam,
            'no_wa' => $this->no_wa,
            'nama_item' => $selectedItem?->nama ?? '',
            'kategori_item' => $selectedItem?->kategori ?? '',
            'tipe_item' => $selectedItem?->tipe ?? '',
            'tanggal_peminjaman' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($this->tanggal_pengembalian)->translatedFormat('d F Y'),
            'jam_mulai' => $this->mode === 'ruangan' ? $this->jam_mulai : null,
            'jam_selesai' => $this->mode === 'ruangan' ? $this->jam_selesai : null,
            'catatan' => $this->catatan . ($this->mode === 'ruangan' ? "\n(Permintaan Kursi: " . $this->jumlah_kursi . " kursi)" : ""),
        ]);

        // Update item status if it's a room
        $item = Item::find($this->selected_item_id);
        if ($item->tipe === 'ruangan') {
            $item->update(['status' => 'dipinjam']);
        }

        // Reset form
        $this->reset(['nama_peminjam', 'instansi_peminjam', 'no_wa', 'bukti_peminjam', 'selected_item_id', 'catatan']);
        $this->tanggal_peminjaman = Carbon::today()->format('Y-m-d');
        $this->tanggal_pengembalian = Carbon::today()->format('Y-m-d');
        $this->loadItems();

        session()->flash('success', 'Permohonan peminjaman berhasil dikirim! Silakan pantau status persetujuan di Dashboard Akun Anda.');
    }
};
?>

<div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-6 sm:p-8 space-y-6">

    {{-- Kategori & Tipe Selector Tabs --}}
    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-1.5 rounded-2xl border border-gray-100">
        <button type="button" wire:click="$set('mode', 'ruangan')"
            class="py-3 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2 {{ $mode === 'ruangan' ? 'bg-teal-700 text-white shadow-sm' : 'text-gray-600 hover:text-teal-700' }}">
            <i class="fas fa-door-open"></i> Peminjaman Studio & Lab (Ruangan)
        </button>
        <button type="button" wire:click="$set('mode', 'peralatan')"
            class="py-3 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-2 {{ $mode === 'peralatan' ? 'bg-teal-700 text-white shadow-sm' : 'text-gray-600 hover:text-teal-700' }}">
            <i class="fas fa-tools"></i> Peminjaman Peralatan
        </button>
    </div>

    @if (session()->has('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-4 rounded-2xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
            <div>
                <span class="font-bold">Sukses!</span> {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm p-4 rounded-2xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
            <div>
                <span class="font-bold">Gagal!</span> {{ session('error') }}
            </div>
        </div>
    @endif

    @if(!Auth::check())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs p-4 rounded-2xl flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i class="fas fa-info-circle text-yellow-500 text-lg"></i>
                <span>Silakan masuk ke akun Anda terlebih dahulu untuk mengisi formulir peminjaman.</span>
            </div>
            <a href="{{ route('login') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold px-3 py-1.5 rounded-lg shrink-0 transition-colors">
                Masuk / Login
            </a>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5 {{ !Auth::check() ? 'opacity-50 pointer-events-none' : '' }}">
        
        {{-- Kategori (Studio / Lab) --}}
        <div>
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Pilih Wilayah Kategori</label>
            <div class="grid grid-cols-2 gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-100">
                <button type="button" wire:click="$set('kategori', 'studio')"
                    class="py-2.5 text-xs font-bold rounded-lg transition-all {{ $kategori === 'studio' ? 'bg-teal-700/80 text-white shadow-sm' : 'text-gray-600 hover:text-teal-700' }}">
                    Studio Penyiaran & Podcast
                </button>
                <button type="button" wire:click="$set('kategori', 'laboratorium')"
                    class="py-2.5 text-xs font-bold rounded-lg transition-all {{ $kategori === 'laboratorium' ? 'bg-teal-700/80 text-white shadow-sm' : 'text-gray-600 hover:text-teal-700' }}">
                    Laboratorium Terpadu
                </button>
            </div>
        </div>

        {{-- Dropdown Item --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Pilih Item (Alat/Ruangan)</label>
            <select wire:model="selected_item_id"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700">
                <option value="">-- Pilih {{ $mode === 'ruangan' ? 'Ruangan' : 'Peralatan' }} Tersedia --</option>
                @foreach($availableItems as $item)
                    <option value="{{ $item->id }}">{{ $item->nama }} (Stok: {{ $item->stok }} unit)</option>
                @endforeach
            </select>
            @error('selected_item_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Biodata Peminjam --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Nama Lengkap Peminjam</label>
                <input type="text" wire:model="nama_peminjam"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                    placeholder="Contoh: Ahmad Fauzi">
                @error('nama_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Instansi / Prodi Peminjam</label>
                <input type="text" wire:model="instansi_peminjam"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                    placeholder="Contoh: Mahasiswa PAI STAIMAS">
                @error('instansi_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Nomor WhatsApp Peminjam --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">
                <i class="fab fa-whatsapp text-green-600 mr-1"></i> Nomor WhatsApp Aktif Peminjam
            </label>
            <div class="flex items-center gap-2">
                <span class="px-3 py-3 bg-green-50 border border-gray-200 rounded-xl text-xs font-bold text-green-700 shrink-0">+62</span>
                <input type="tel" wire:model="no_wa"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Contoh: 0812-3456-7890">
            </div>
            <p class="text-[10px] text-gray-400">Nomor ini digunakan untuk konfirmasi balik dari petugas UPT.</p>
            @error('no_wa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>


        {{-- Waktu Booking: Jam (Ruangan) vs Hari (Barang) --}}
        @if($mode === 'ruangan')
            <div class="p-4 bg-teal-50/50 border border-teal-100 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-teal-950 uppercase tracking-wider block">Tanggal Sewa</label>
                    <input type="date" wire:model.live="tanggal_peminjaman"
                        class="w-full px-3 py-2 rounded-xl border border-teal-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('tanggal_peminjaman') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-teal-950 uppercase tracking-wider block">Jam Mulai</label>
                    <input type="time" wire:model="jam_mulai"
                        class="w-full px-3 py-2 rounded-xl border border-teal-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('jam_mulai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-teal-950 uppercase tracking-wider block">Jam Selesai</label>
                    <input type="time" wire:model="jam_selesai"
                        class="w-full px-3 py-2 rounded-xl border border-teal-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('jam_selesai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            @if($maxKapasitasKursi > 0)
                <div class="p-4 bg-teal-50/20 border border-teal-100/50 rounded-2xl space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-teal-950 uppercase tracking-wider"><i class="fas fa-chair text-teal-600"></i> Kursi yang Dibutuhkan</span>
                        <span class="text-teal-700 font-semibold bg-teal-50 px-2 py-0.5 rounded border border-teal-100">Maksimal: {{ $maxKapasitasKursi }} Kursi</span>
                    </div>
                    <input type="number" wire:model="jumlah_kursi" min="1" max="{{ $maxKapasitasKursi }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-teal-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-teal-700"
                        placeholder="Masukkan jumlah kursi, misal: 2">
                    @error('jumlah_kursi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            @endif
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Tanggal Mulai Peminjaman</label>
                    <input type="date" wire:model.live="tanggal_peminjaman"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('tanggal_peminjaman') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Tanggal Selesai / Pengembalian</label>
                    <input type="date" wire:model="tanggal_pengembalian"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('tanggal_pengembalian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        {{-- Penanggung Jawab Harian (Dinamis) --}}
        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-sm">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">PJ UPT Bertugas Hari Ini</span>
                    <span class="text-xs font-extrabold text-gray-900">{{ $currentPjName }}</span>
                </div>
            </div>
            <span class="text-[10px] text-teal-700 font-semibold bg-teal-50/50 px-2 py-0.5 rounded-md border border-teal-100">
                Otomatis Tersemat
            </span>
        </div>

        {{-- Upload KTM / KTP --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Bukti Identitas Peminjam (KTM/KTP)</label>
            <div class="flex items-center justify-center w-full">
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if ($bukti_peminjam)
                            <i class="fas fa-file-image text-teal-700 text-2xl mb-2"></i>
                            <p class="text-xs text-teal-800 font-semibold">{{ $bukti_peminjam->getClientOriginalName() }}</p>
                        @else
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl mb-2"></i>
                            <p class="text-xs text-gray-500"><span class="font-bold">Klik untuk mengunggah</span> atau drag and drop</p>
                            <p class="text-[10px] text-gray-400">PNG, JPG atau JPEG (Max 2MB)</p>
                        @endif
                    </div>
                    <input type="file" wire:model="bukti_peminjam" class="hidden" accept="image/*" />
                </label>
            </div>
            @error('bukti_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            <div wire:loading wire:target="bukti_peminjam" class="text-xs text-teal-700 mt-1">
                <i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah berkas...
            </div>
        </div>

        {{-- Catatan Tambahan --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block">Catatan / Keperluan Peminjaman</label>
            <textarea wire:model="catatan" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 leading-relaxed"
                placeholder="Tuliskan alasan peminjaman atau rincian keperluan praktikum..."></textarea>
            @error('catatan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" wire:loading.attr="disabled"
            class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all shadow-md shadow-teal-700/20 flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="save"><i class="fas fa-paper-plane"></i> Ajukan Peminjaman</span>
            <span wire:loading wire:target="save"><i class="fas fa-spinner fa-spin"></i> Memproses pengajuan...</span>
        </button>

    </form>
</div>
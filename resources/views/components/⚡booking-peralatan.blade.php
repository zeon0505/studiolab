<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Item;
use App\Models\Booking;
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
    public mixed $bukti_peminjam = null;
    public ?string $tanggal_peminjaman = null;
    public ?string $tanggal_pengembalian = null;
    public ?string $no_wa = null;
    public ?string $catatan = null;

    public string $kategori = 'studio';
    public string $selected_item_id = '';
    public ?array $selectedItemData = null;

    public string $currentPjName = '';
    public ?int $currentPjId = null;

    public $availableItems = [];
    public $listHari = [];

    // Step: 'tanggal' | 'form' | 'review'
    public $currentStep = 'tanggal';

    public function mount(int $selected_item_id = 0)
    {
        $this->generateListHari();
        $this->tanggal_peminjaman  = count($this->listHari) > 0 ? $this->listHari[0]['date'] : Carbon::today()->format('Y-m-d');
        $this->tanggal_pengembalian = $this->tanggal_peminjaman;

        $this->loadItems();
        $this->checkPjAssignment();

        // Pre-select item jika dikirim dari halaman form
        if ($selected_item_id) {
            $this->selected_item_id = $selected_item_id;
            $item = Item::find($selected_item_id);
            if ($item) {
                $this->kategori = $item->kategori;
                $this->selectedItemData = $item->toArray();
                $this->loadItems();
            }
        }
    }

    public function generateListHari()
    {
        $list = [];
        $dayMap = [
            'Monday' => 'SEN', 'Tuesday' => 'SEL', 'Wednesday' => 'RAB',
            'Thursday' => 'KAM', 'Friday' => 'JUM', 'Saturday' => 'SAB',
        ];
        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::today()->addDays($i);
            if ($date->isSunday()) continue;
            $list[] = [
                'date'    => $date->format('Y-m-d'),
                'day_num' => $date->format('d'),
                'day_lbl' => $dayMap[$date->format('l')] ?? '',
                'month'   => strtoupper($date->translatedFormat('M')),
                'is_today' => $date->isToday(),
            ];
        }
        $this->listHari = $list;
    }

    public function pilihTanggal(string $date)
    {
        $this->tanggal_peminjaman  = $date;
        $this->tanggal_pengembalian = $date;
        $this->checkPjAssignment();
    }

    public function pilihTanggalKembali(string $date)
    {
        $this->tanggal_pengembalian = $date;
    }

    public function lanjutKeForm()
    {
        $this->currentStep = 'form';
    }

    public function kembaliKeTanggal()
    {
        $this->currentStep = 'tanggal';
    }

    public function lanjutKeReview()
    {
        $this->validate([
            'nama_peminjam'     => 'required|string|max:255',
            'instansi_peminjam' => 'required|string|max:255',
            'no_wa'             => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'bukti_peminjam'    => 'required|image|max:2048',
            'selected_item_id'  => 'required',
        ], [
            'selected_item_id.required' => 'Silakan pilih peralatan yang ingin dipinjam.',
            'bukti_peminjam.required'   => 'Silakan unggah foto KTM/KTP sebagai bukti identitas.',
        ]);
        $this->currentStep = 'review';
    }

    public function kembaliKeForm()
    {
        $this->currentStep = 'form';
    }

    public function updatedKategori()
    {
        $this->selected_item_id = '';
        $this->selectedItemData = null;
        $this->loadItems();
    }

    public function updatedTanggalPeminjaman()
    {
        $this->checkPjAssignment();
    }

    public function selectItem(int $id)
    {
        $this->selected_item_id = $id;
        $item = collect($this->availableItems)->firstWhere('id', $id);
        $this->selectedItemData = $item ? (array)$item : null;
    }

    public function loadItems()
    {
        $items = Item::where('kategori', $this->kategori)
            ->where('tipe', 'peralatan')
            ->where('status', 'tersedia')
            ->get();

        $this->availableItems = $items->map(fn($item) => [
            'id'       => $item->id,
            'nama'     => $item->nama,
            'stok'     => $item->stok,
            'deskripsi'=> $item->deskripsi,
            'gambar'   => $item->gambar,
            'status'   => $item->status,
        ])->toArray();

        if ($this->selected_item_id) {
            $found = collect($this->availableItems)->firstWhere('id', $this->selected_item_id);
            if (!$found) {
                $this->selected_item_id = '';
                $this->selectedItemData = null;
            }
        }
    }

    public function checkPjAssignment()
    {
        if (empty($this->tanggal_peminjaman)) {
            $this->currentPjName = 'Tidak ada tanggal dipilih';
            $this->currentPjId   = null;
            return;
        }
        $carbonDate = Carbon::parse($this->tanggal_peminjaman);
        if ($carbonDate->isSunday()) {
            $this->currentPjName = 'UPT Tutup (Hari Minggu)';
            $this->currentPjId   = null;
            return;
        }
        $dayMap = ['monday'=>'senin','tuesday'=>'selasa','wednesday'=>'rabu','thursday'=>'kamis','friday'=>'jumat','saturday'=>'sabtu'];
        $dayName = $dayMap[strtolower($carbonDate->format('l'))] ?? 'senin';
        $assignment = DailyAssignment::with('user')->where('hari', $dayName)->first();
        if ($assignment && $assignment->user) {
            $this->currentPjName = $assignment->user->name;
            $this->currentPjId   = $assignment->user->id;
        } else {
            $this->currentPjName = 'Belum ditentukan oleh Admin';
            $this->currentPjId   = null;
        }
    }

    public function save()
    {
        $this->validate([
            'nama_peminjam'       => 'required|string|max:255',
            'instansi_peminjam'   => 'required|string|max:255',
            'no_wa'               => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'selected_item_id'    => 'required',
            'tanggal_peminjaman'  => 'required|date',
            'tanggal_pengembalian'=> 'required|date|after_or_equal:tanggal_peminjaman',
        ], [
            'selected_item_id.required'          => 'Silakan pilih peralatan yang ingin dipinjam.',
            'tanggal_pengembalian.after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
        ]);

        // Pastikan file bukti masih ada
        if (!$this->bukti_peminjam) {
            session()->flash('error', 'File identitas tidak ditemukan. Silakan upload ulang KTM/KTP.');
            $this->currentStep = 'form';
            return;
        }

        if (empty($this->currentPjId)) {
            session()->flash('error', 'Peminjaman gagal. Tidak ada PJ bertugas pada hari tersebut. Hubungi admin.');
            return;
        }

        // Real-time Stock validation guard (mencegah double booking/race condition)
        $item = Item::find($this->selected_item_id);
        if (!$item || $item->status !== 'tersedia' || $item->stok < 1) {
            session()->flash('error', 'Maaf, peralatan yang Anda pilih baru saja dipinjam atau tidak lagi tersedia. Silakan pilih peralatan lain.');
            $this->loadItems();
            $this->currentStep = 'tanggal';
            return;
        }

        $path = $this->bukti_peminjam->store('proofs', 'public');

        // Kurangi stok atau update status peralatan jika perlu
        if ($item->stok <= 1) {
            $item->update([
                'stok' => 0,
                'status' => 'dipinjam'
            ]);
        } else {
            $item->decrement('stok');
        }

        $booking = Booking::create([
            'item_id'              => $this->selected_item_id,
            'user_id'              => Auth::id(),
            'penanggung_jawab_id'  => $this->currentPjId,
            'nama_peminjam'        => $this->nama_peminjam,
            'instansi_peminjam'    => $this->instansi_peminjam,
            'no_wa'                => $this->no_wa,
            'bukti_peminjam'       => $path,
            'tanggal_peminjaman'   => $this->tanggal_peminjaman,
            'tanggal_pengembalian' => $this->tanggal_pengembalian,
            'jam_mulai'            => null,
            'jam_selesai'          => null,
            'jumlah_kursi'         => 0,
            'status'               => 'pending',
            'catatan'              => $this->catatan,
        ]);

        $booking->load('item', 'penanggungJawab');
        $whatsapp = app(WhatsAppService::class);

        $whatsapp->notifyPj([
            'booking_id'         => $booking->id,
            'pj_name'            => $booking->penanggungJawab?->name ?? 'PJ Bertugas',
            'pj_no_wa'           => $booking->penanggungJawab?->no_wa ?? '',
            'nama_peminjam'      => $this->nama_peminjam,
            'instansi_peminjam'  => $this->instansi_peminjam,
            'no_wa'              => $this->no_wa,
            'nama_item'          => $booking->item->nama,
            'kategori_item'      => $booking->item->kategori,
            'tipe_item'          => $booking->item->tipe,
            'tanggal_peminjaman' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($this->tanggal_pengembalian)->translatedFormat('d F Y'),
            'jam_mulai'          => null,
            'jam_selesai'        => null,
            'catatan'            => $this->catatan,
        ]);

        $whatsapp->notifyPeminjam([
            'booking_id'         => $booking->id,
            'nama_peminjam'      => $this->nama_peminjam,
            'no_wa'              => $this->no_wa,
            'nama_item'          => $booking->item->nama,
            'kategori_item'      => $booking->item->kategori,
            'tipe_item'          => $booking->item->tipe,
            'tanggal_peminjaman' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($this->tanggal_pengembalian)->translatedFormat('d F Y'),
            'jam_mulai'          => null,
            'jam_selesai'        => null,
            'catatan'            => $this->catatan,
        ]);

        $this->reset(['nama_peminjam','instansi_peminjam','no_wa','bukti_peminjam','catatan']);
        $this->selected_item_id  = '';
        $this->selectedItemData  = null;
        $this->tanggal_peminjaman  = count($this->listHari) > 0 ? $this->listHari[0]['date'] : Carbon::today()->format('Y-m-d');
        $this->tanggal_pengembalian = $this->tanggal_peminjaman;
        $this->currentStep = 'tanggal';
        $this->loadItems();

        // Simpan ringkasan booking ke session untuk ditampilkan di layar sukses dengan QR Code
        session()->flash('booking_sukses', [
            'id'        => $booking->id,
            'kode'      => 'BKG-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
            'nama'      => $booking->nama_peminjam,
            'item'      => $booking->item->nama,
            'tanggal'   => Carbon::parse($booking->tanggal_peminjaman)->translatedFormat('d F Y'),
            'kembali'   => Carbon::parse($booking->tanggal_pengembalian)->translatedFormat('d F Y'),
        ]);
    }
};
?>

<div class="space-y-5">

    {{-- ===== PROGRESS STEPPER ===== --}}
    @php
        $steps = $selectedItemData
            ? [['key'=>'tanggal','label'=>'Tanggal'], ['key'=>'form','label'=>'Data Diri'], ['key'=>'review','label'=>'Konfirmasi']]
            : [['key'=>'tanggal','label'=>'Tanggal'], ['key'=>'form','label'=>'Pilih & Data'], ['key'=>'review','label'=>'Konfirmasi']];
        $stepOrder = array_column($steps, 'key');
        $currentIdx = array_search($currentStep, $stepOrder);
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-4">
        <div class="flex items-center justify-between relative">
            {{-- Garis penghubung --}}
            <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-slate-100 -translate-y-1/2 mx-8 z-0"></div>
            <div class="absolute left-0 top-1/2 h-0.5 bg-teal-600 -translate-y-1/2 mx-8 z-0 transition-all duration-500
                        {{ $currentIdx === 0 ? 'w-0' : ($currentIdx === 1 ? 'w-1/2' : 'w-full') }}"></div>

            @foreach($steps as $i => $step)
                @php $done = $i < $currentIdx; $active = $i === $currentIdx; @endphp
                <div class="flex flex-col items-center gap-1.5 z-10">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300
                                {{ $done ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : ($active ? 'bg-teal-700 text-white ring-4 ring-teal-100 shadow-lg shadow-teal-700/30' : 'bg-slate-100 text-slate-400') }}">
                        @if($done) <i class="fas fa-check text-[10px]"></i>
                        @else {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="text-[10px] font-bold {{ $active ? 'text-teal-700' : ($done ? 'text-teal-500' : 'text-slate-400') }} whitespace-nowrap">
                        {{ $step['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== ALERT SUKSES + QR CODE ===== --}}
    @if(session()->has('booking_sukses'))
        @php $bs = session('booking_sukses'); @endphp
        @php $qrData = urlencode(route('admin.bookings.show', $bs['id'])); @endphp
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-3xl overflow-hidden shadow-md shadow-emerald-100">
            {{-- Top bar --}}
            <div class="bg-emerald-600 text-white px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <i class="fas fa-check text-sm"></i>
                </div>
                <div>
                    <p class="font-black text-sm">Permohonan Terkirim! 🎉</p>
                    <p class="text-emerald-100 text-[10px]">Menunggu persetujuan PJ UPT</p>
                </div>
                <span class="ml-auto font-mono text-[11px] bg-white/20 px-2.5 py-1 rounded-lg font-bold">{{ $bs['kode'] }}</span>
            </div>
            {{-- Body --}}
            <div class="p-5 flex items-start gap-5">
                {{-- QR Code --}}
                <div class="shrink-0 text-center">
                    <div class="bg-white p-2 rounded-2xl shadow-sm border border-emerald-100 inline-block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ $qrData }}"
                             alt="QR Bukti {{ $bs['kode'] }}"
                             class="w-28 h-28 rounded-lg">
                    </div>
                    <p class="text-[9px] text-emerald-600 font-bold mt-1.5 uppercase tracking-wider mb-2">Bukti Booking</p>
                    <button type="button" data-qr="{{ $qrData }}" data-filename="{{ $bs['kode'] }}" onclick="downloadQRCode(this)"
                            class="inline-flex items-center gap-1.5 bg-white border border-emerald-200 hover:bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1.5 rounded-xl transition-all shadow-sm">
                        <i class="fas fa-download text-[9px]"></i> Unduh QR
                    </button>
                </div>
                {{-- Info --}}
                <div class="flex-1 space-y-2">
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Item Dipinjam</p>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $bs['item'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Tanggal Pinjam</p>
                        <p class="font-semibold text-slate-700 text-sm mt-0.5">{{ $bs['tanggal'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Rencana Kembali</p>
                        <p class="font-semibold text-slate-700 text-sm mt-0.5">{{ $bs['kembali'] }}</p>
                    </div>
                    <div class="pt-2 border-t border-emerald-200 mt-2">
                        <p class="text-[10px] text-emerald-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Screenshot QR ini sebagai bukti. Tunjukkan kepada PJ saat pengambilan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm p-4 rounded-2xl flex items-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-red-400 text-xl shrink-0"></i>
            <div>
                <p class="font-bold">Terjadi Masalah</p>
                <p class="text-red-700 text-xs mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP A: PILIH TANGGAL --}}
    {{-- ============================================================ --}}
    @if($currentStep === 'tanggal')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-calendar-alt text-teal-600"></i> Pilih Tanggal Peminjaman
        </h4>

        {{-- Scrollable date picker --}}
        <div class="flex gap-2.5 overflow-x-auto pb-2 snap-x snap-mandatory scrollbar-hide">
            @foreach($listHari as $hari)
                @php $isSelected = $tanggal_peminjaman === $hari['date']; @endphp
                <button type="button" wire:click="pilihTanggal('{{ $hari['date'] }}')"
                    class="snap-start shrink-0 w-14 flex flex-col items-center py-3 px-1 rounded-2xl border-2 transition-all duration-200 font-bold
                           {{ $isSelected
                              ? 'bg-teal-700 border-teal-700 text-white shadow-lg shadow-teal-700/30 scale-105'
                              : 'border-slate-100 text-slate-600 hover:border-teal-300 hover:text-teal-700 bg-slate-50' }}">
                    <span class="text-[9px] uppercase tracking-widest {{ $isSelected ? 'text-teal-200' : 'text-slate-400' }}">{{ $hari['day_lbl'] }}</span>
                    <span class="text-xl font-black leading-tight">{{ $hari['day_num'] }}</span>
                    <span class="text-[9px] {{ $isSelected ? 'text-teal-200' : 'text-slate-400' }}">{{ $hari['month'] }}</span>
                    @if($hari['is_today'])
                        <span class="w-1.5 h-1.5 rounded-full mt-1 {{ $isSelected ? 'bg-white' : 'bg-teal-500' }}"></span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Tanggal Pengembalian --}}
        <div class="mt-5 p-4 bg-slate-50 border border-slate-100 rounded-2xl">
            <label class="text-[10px] font-black text-slate-500 uppercase tracking-wider block mb-2">
                <i class="fas fa-calendar-check text-teal-600 mr-1"></i> Rencana Tanggal Pengembalian
            </label>
            <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                @foreach($listHari as $hari)
                    @php
                        $isAfter = $hari['date'] >= $tanggal_peminjaman;
                        $isSelectedKembali = $tanggal_pengembalian === $hari['date'];
                    @endphp
                    @if($isAfter)
                    <button type="button" wire:click="pilihTanggalKembali('{{ $hari['date'] }}')"
                        class="shrink-0 flex flex-col items-center py-2.5 px-2 rounded-xl border-2 text-xs font-bold transition-all
                               {{ $isSelectedKembali
                                  ? 'bg-teal-600 border-teal-600 text-white shadow-md shadow-teal-600/20'
                                  : 'border-slate-200 text-slate-500 hover:border-teal-300 bg-white' }}">
                        <span class="text-[8px] {{ $isSelectedKembali ? 'text-teal-200' : 'text-slate-400' }}">{{ $hari['day_lbl'] }}</span>
                        <span class="text-sm font-black">{{ $hari['day_num'] }}</span>
                    </button>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- PJ Info --}}
        <div class="mt-4 flex items-center gap-3 p-3.5 bg-teal-50 border border-teal-100 rounded-2xl">
            <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
                <i class="fas fa-user-shield text-teal-600 text-sm"></i>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-teal-500">PJ Bertugas Hari Ini</p>
                <p class="text-sm font-bold text-teal-900">{{ $currentPjName ?: 'Memuat...' }}</p>
            </div>
        </div>

        {{-- Pilih Peralatan (jika belum pre-select) --}}
        @if(!$selectedItemData)
        <div class="mt-5 border-t border-slate-100 pt-5">
            <h5 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-tools text-teal-600"></i> Pilih Peralatan
            </h5>
            <div class="flex gap-2 mb-4">
                <button type="button" wire:click="$set('kategori', 'studio')"
                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border-2 transition-all
                           {{ $kategori === 'studio' ? 'bg-teal-700 border-teal-700 text-white' : 'border-slate-200 text-slate-600 hover:border-teal-300' }}">
                    <i class="fas fa-broadcast-tower"></i> Studio
                </button>
                <button type="button" wire:click="$set('kategori', 'laboratorium')"
                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold border-2 transition-all
                           {{ $kategori === 'laboratorium' ? 'bg-teal-700 border-teal-700 text-white' : 'border-slate-200 text-slate-600 hover:border-teal-300' }}">
                    <i class="fas fa-flask"></i> Laboratorium
                </button>
            </div>
            @if(count($availableItems) === 0)
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-tools text-3xl mb-2 opacity-40"></i>
                    <p class="text-sm font-medium">Tidak ada peralatan tersedia saat ini.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($availableItems as $itm)
                        @php
                            $isSel = $selected_item_id == $itm['id'];
                            $gUrl  = $itm['gambar'] ? asset('storage/' . $itm['gambar']) : null;
                        @endphp
                        <button type="button" wire:click="selectItem({{ $itm['id'] }})"
                            class="relative text-left rounded-2xl border-2 overflow-hidden transition-all duration-200 group
                                   {{ $isSel ? 'border-teal-600 shadow-xl shadow-teal-600/20 scale-[1.03]' : 'border-slate-200 hover:border-teal-400 hover:shadow-lg' }}">
                            <div class="relative h-28 bg-gradient-to-br from-slate-700 to-slate-900">
                                @if($gUrl)
                                    <img src="{{ $gUrl }}" alt="{{ $itm['nama'] }}" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-tools text-slate-400 text-3xl opacity-50"></i>
                                    </div>
                                @endif
                                <span class="absolute top-2 right-2 text-[9px] font-black bg-emerald-500 text-white px-2 py-0.5 rounded-full">{{ $itm['stok'] }} unit</span>
                                @if($isSel)
                                    <div class="absolute inset-0 bg-teal-700/30 flex items-center justify-center">
                                        <div class="w-9 h-9 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                            <i class="fas fa-check text-teal-700 text-base"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2.5 bg-white">
                                <p class="font-bold text-slate-900 text-[11px] leading-snug line-clamp-2">{{ $itm['nama'] }}</p>
                                @if($isSel)
                                    <div class="mt-1.5 text-[9px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-lg flex items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Dipilih
                                    </div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
            @error('selected_item_id') <p class="text-red-500 text-xs mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p> @enderror
        </div>
        @else
        {{-- Item sudah pre-select, tampilkan chip konfirmasi --}}
        <div class="mt-5 flex items-center gap-3 p-3.5 bg-slate-50 border border-slate-100 rounded-2xl">
            <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
                <i class="fas fa-tools text-teal-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Peralatan Dipilih</p>
                <p class="text-sm font-bold text-slate-900 truncate">{{ $selectedItemData['nama'] ?? '-' }}</p>
            </div>
            <span class="text-[9px] font-black bg-teal-600 text-white px-2.5 py-1 rounded-lg">✓ Terpilih</span>
        </div>
        @endif

        {{-- Tombol Lanjut --}}
        <button type="button" wire:click="lanjutKeForm"
            @if(!$selected_item_id) disabled @endif
            class="mt-5 w-full py-3.5 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2
                   {{ $selected_item_id ? 'bg-teal-700 hover:bg-teal-800 text-white shadow-lg shadow-teal-900/20 hover:scale-[1.01]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
            Lanjut Isi Data Diri <i class="fas fa-arrow-right text-xs"></i>
        </button>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP B: FORM DATA PEMINJAM --}}
    {{-- ============================================================ --}}
    @if($currentStep === 'form')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">

        {{-- Summary chip item & tanggal --}}
        <div class="grid grid-cols-2 gap-3 p-4 bg-teal-950 rounded-2xl text-white text-xs">
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Peralatan</p>
                <p class="font-bold mt-0.5 truncate">{{ $selectedItemData['nama'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Tanggal Pinjam</p>
                <p class="font-bold mt-0.5">{{ $tanggal_peminjaman ? Carbon::parse($tanggal_peminjaman)->translatedFormat('d M Y') : '-' }}</p>
            </div>
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Tanggal Kembali</p>
                <p class="font-bold mt-0.5">{{ $tanggal_pengembalian ? Carbon::parse($tanggal_pengembalian)->translatedFormat('d M Y') : '-' }}</p>
            </div>
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">PJ Bertugas</p>
                <p class="font-bold mt-0.5 truncate">{{ $currentPjName ?: '-' }}</p>
            </div>
        </div>

        {{-- Nama & Instansi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" wire:model="nama_peminjam"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 transition"
                    placeholder="Contoh: Ahmad Fauzi">
                @error('nama_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Instansi / Prodi <span class="text-red-500">*</span></label>
                <input type="text" wire:model="instansi_peminjam"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 transition"
                    placeholder="Contoh: Mahasiswa PAI STAIMAS">
                @error('instansi_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- WhatsApp --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">
                <i class="fab fa-whatsapp text-green-600 mr-1"></i> Nomor WhatsApp Aktif <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="px-3 py-3 bg-green-50 border border-gray-200 rounded-xl text-xs font-bold text-green-700 shrink-0">+62</span>
                <input type="tel" wire:model="no_wa"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition"
                    placeholder="0812-3456-7890">
            </div>
            <p class="text-[10px] text-gray-400">Nomor ini untuk konfirmasi dari petugas UPT via WhatsApp.</p>
            @error('no_wa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Upload KTM/KTP --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">
                Bukti Identitas KTM / KTP <span class="text-red-500">*</span>
            </label>
            <label class="flex items-center gap-4 p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50 hover:bg-teal-50/50 hover:border-teal-300 cursor-pointer transition-all group">
                <div class="w-16 h-16 rounded-xl bg-white border border-gray-200 group-hover:border-teal-200 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                    @if($this->bukti_peminjam && !is_string($this->bukti_peminjam) && method_exists($this->bukti_peminjam, 'temporaryUrl'))
                        <img src="{{ $this->bukti_peminjam->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif($this->bukti_peminjam)
                        <i class="fas fa-file-image text-teal-600 text-xl"></i>
                    @else
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-xl"></i>
                    @endif
                </div>
                <div class="flex-1">
                    @if($this->bukti_peminjam)
                        <p class="text-sm font-bold text-teal-700 truncate max-w-[200px]">{{ is_string($this->bukti_peminjam) ? $this->bukti_peminjam : $this->bukti_peminjam->getClientOriginalName() }}</p>
                        <p class="text-[10px] text-teal-500 mt-0.5">Klik untuk ganti file</p>
                    @else
                        <p class="text-sm font-semibold text-gray-600">Seret foto atau <span class="text-teal-700 underline">klik untuk upload</span></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, JPEG — Maksimal 2MB</p>
                    @endif
                </div>
                <input type="file" wire:model="bukti_peminjam" class="hidden" accept="image/*">
            </label>
            <div wire:loading wire:target="bukti_peminjam" class="text-xs text-teal-700 flex items-center gap-1.5 mt-1">
                <i class="fas fa-spinner fa-spin"></i> Mengunggah berkas...
            </div>
            @error('bukti_peminjam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        {{-- Catatan --}}
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Tujuan / Alasan Peminjaman</label>
            <textarea wire:model="catatan" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700 leading-relaxed transition"
                placeholder="Contoh: Kebutuhan liputan berita tugas mata kuliah jurnalistik penyiaran..."></textarea>
        </div>

        {{-- Navigasi --}}
        <div class="flex gap-3 pt-1">
            <button type="button" wire:click="kembaliKeTanggal"
                class="flex-1 py-3.5 rounded-2xl font-bold text-sm border-2 border-slate-200 text-slate-600 hover:bg-slate-50 transition flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Kembali
            </button>
            <button type="button" wire:click="lanjutKeReview"
                class="flex-1 py-3.5 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm shadow-lg shadow-teal-900/20 transition-all hover:scale-[1.01] flex items-center justify-center gap-2">
                Tinjau & Konfirmasi <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP C: REVIEW & KONFIRMASI --}}
    {{-- ============================================================ --}}
    @if($currentStep === 'review')
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-teal-900 to-teal-700 px-6 py-5 text-white">
            <p class="text-[10px] font-black uppercase tracking-widest text-teal-300 mb-1">Konfirmasi Peminjaman</p>
            <h4 class="font-extrabold text-lg">Cek kembali pesanan Anda</h4>
            <p class="text-teal-300 text-xs mt-0.5">Pastikan semua data sudah benar sebelum mengirim permohonan.</p>
        </div>

        {{-- Detail rows --}}
        <div class="divide-y divide-slate-50 p-6 space-y-0">
            @php
                $rows = [
                    ['icon'=>'fa-tools','label'=>'Peralatan','value'=>$selectedItemData['nama'] ?? '-'],
                    ['icon'=>'fa-calendar','label'=>'Tanggal Pinjam','value'=> $tanggal_peminjaman ? Carbon::parse($tanggal_peminjaman)->translatedFormat('l, d F Y') : '-'],
                    ['icon'=>'fa-calendar-check','label'=>'Tanggal Kembali','value'=> $tanggal_pengembalian ? Carbon::parse($tanggal_pengembalian)->translatedFormat('l, d F Y') : '-'],
                    ['icon'=>'fa-user','label'=>'Nama Peminjam','value'=>$nama_peminjam ?? '-'],
                    ['icon'=>'fa-university','label'=>'Instansi / Prodi','value'=>$instansi_peminjam ?? '-'],
                    ['icon'=>'fa-phone-alt','label'=>'WhatsApp','value'=>'+62 ' . ($no_wa ?? '-')],
                    ['icon'=>'fa-user-shield','label'=>'PJ Bertugas','value'=>$currentPjName ?: '-'],
                    ['icon'=>'fa-comment-alt','label'=>'Catatan','value'=>$catatan ?: '(tidak ada)'],
                ];
            @endphp
            @foreach($rows as $row)
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas {{ $row['icon'] }} text-teal-600 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ $row['label'] }}</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5 break-words">{{ $row['value'] }}</p>
                </div>
            </div>
            @endforeach

            {{-- Bukti --}}
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-id-card text-teal-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Bukti KTM/KTP</p>
                    @if($this->bukti_peminjam)
                        <p class="text-sm font-semibold text-emerald-700 mt-0.5 flex items-center gap-1.5">
                            <i class="fas fa-check-circle text-emerald-500"></i> 
                            {{ is_string($this->bukti_peminjam) ? $this->bukti_peminjam : (method_exists($this->bukti_peminjam, 'getClientOriginalName') ? $this->bukti_peminjam->getClientOriginalName() : 'Identitas Terunggah') }}
                        </p>
                    @else
                        <p class="text-sm text-red-500 mt-0.5">Belum diupload</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Navigasi --}}
        <div class="px-6 pb-6 flex gap-3">
            <button type="button" wire:click="kembaliKeForm"
                class="flex-1 py-3.5 rounded-2xl font-bold text-sm border-2 border-slate-200 text-slate-600 hover:bg-slate-50 transition flex items-center justify-center gap-2">
                <i class="fas fa-pencil-alt text-xs"></i> Edit Data
            </button>
            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="flex-1 py-3.5 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm shadow-lg shadow-teal-900/20 transition-all disabled:opacity-70 flex items-center justify-center">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <i class="fas fa-paper-plane text-xs"></i> Kirim Permohonan
                </span>
                <span wire:loading wire:target="save" class="hidden flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin text-xs"></i> Memproses...
                </span>
            </button>
        </div>
    </div>
    @endif

    {{-- Javascript Unduh QR Code --}}
    <script>
        function downloadQRCode(btn) {
            const qrData = btn.getAttribute('data-qr');
            const filename = btn.getAttribute('data-filename');
            const url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + qrData;
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename + '.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                })
                .catch(err => {
                    alert('Gagal mengunduh QR Code. Silakan screenshot manual.');
                });
        }
    </script>

</div>


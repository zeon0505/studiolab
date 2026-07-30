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

    // --- STATE KATALOG ---
    public $filterKategori = '';   // '' | 'studio' | 'laboratorium'
    public $filterTipe = '';       // '' | 'peralatan' | 'ruangan'
    public $allItems = [];

    // --- STATE DRAWER FORM ---
    public $selectedItem = null;   // array data item yang dipilih

    // --- FORM FIELDS ---
    public $nama_peminjam = '';
    public $instansi_peminjam = '';
    public $no_wa = '';
    public $bukti_peminjam;
    public $tanggal_peminjaman = '';
    public $tanggal_pengembalian = '';
    public $catatan = '';

    // Ruangan khusus
    public $jam_mulai = '08:00';
    public $jam_selesai = '10:00';
    public $jumlah_kursi = 1;
    public $maxKapasitasKursi = 0;
    public $modeJam = 'default';
    public $sesiDipilih = 'sesi1';
    public $listHari = [];
    public $conflictMessage = '';

    public $sesiJam = [
        'sesi1' => ['label' => 'Pagi',   'jam' => '08:00 – 10:00', 'mulai' => '08:00', 'selesai' => '10:00', 'icon' => '🌅'],
        'sesi2' => ['label' => 'Siang',  'jam' => '10:00 – 12:00', 'mulai' => '10:00', 'selesai' => '12:00', 'icon' => '☀️'],
        'sesi3' => ['label' => 'Sore',   'jam' => '13:00 – 15:00', 'mulai' => '13:00', 'selesai' => '15:00', 'icon' => '🌤️'],
        'sesi4' => ['label' => 'Petang', 'jam' => '15:00 – 17:00', 'mulai' => '15:00', 'selesai' => '17:00', 'icon' => '🌆'],
    ];

    // PJ
    public $currentPjName = '';
    public $currentPjId = null;

    public function mount()
    {
        $this->tanggal_peminjaman = Carbon::today()->format('Y-m-d');
        $this->tanggal_pengembalian = Carbon::today()->format('Y-m-d');
        $this->generateListHari();
        $this->loadItems();
    }

    public function generateListHari()
    {
        $list = [];
        $dayMap = ['Monday'=>'SEN','Tuesday'=>'SEL','Wednesday'=>'RAB','Thursday'=>'KAM','Friday'=>'JUM','Saturday'=>'SAB'];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::today()->addDays($i);
            if ($date->isSunday()) continue;
            $list[] = [
                'date'       => $date->format('Y-m-d'),
                'day_num'    => $date->format('d'),
                'month_name' => strtoupper($date->translatedFormat('M')),
                'day_name'   => $dayMap[$date->format('l')] ?? 'SEN',
            ];
            if (count($list) === 7) break;
        }
        $this->listHari = $list;
    }

    public function loadItems()
    {
        $today = Carbon::today()->format('Y-m-d');
        $query = Item::query();
        if ($this->filterKategori) $query->where('kategori', $this->filterKategori);
        if ($this->filterTipe)     $query->where('tipe',     $this->filterTipe);
        $this->allItems = $query->latest()->get()->map(function($i) use ($today) {
            $sesiBisaDipinjam = true;
            $sesiTersisaCount = count($this->sesiJam); // default semua tersedia

            if ($i->tipe === 'ruangan') {
                // Hitung berapa sesi yang sudah penuh untuk hari ini
                $sesiPenuh = 0;
                foreach ($this->sesiJam as $sesi) {
                    $conflict = Booking::where('item_id', $i->id)
                        ->whereIn('status', ['pending', 'disetujui'])
                        ->where('tanggal_peminjaman', $today)
                        ->where('jam_mulai', '<', $sesi['selesai'])
                        ->where('jam_selesai', '>', $sesi['mulai'])
                        ->exists();
                    if ($conflict) $sesiPenuh++;
                }
                $sesiTersisaCount = count($this->sesiJam) - $sesiPenuh;
                // Hanya dikunci jika SEMUA sesi penuh
                $sesiBisaDipinjam = $sesiTersisaCount > 0;
            } else {
                // Peralatan: pakai status DB
                $sesiBisaDipinjam = $i->status === 'tersedia';
                $sesiTersisaCount = $sesiBisaDipinjam ? $i->stok : 0;
            }

            return [
                'id'              => $i->id,
                'nama'            => $i->nama,
                'kategori'        => $i->kategori,
                'tipe'            => $i->tipe,
                'deskripsi'       => $i->deskripsi,
                'gambar'          => $i->gambar,
                'status'          => $i->status,
                'stok'            => $i->stok,
                'kapasitas_kursi' => $i->kapasitas_kursi ?? 0,
                'sesiBisaDipinjam'=> $sesiBisaDipinjam,
                'sesiTersisaCount'=> $sesiTersisaCount,
            ];
        })->toArray();
    }

    public function updatedFilterKategori() { $this->loadItems(); }
    public function updatedFilterTipe()     { $this->loadItems(); }

    // ── Buka drawer untuk item tertentu ────────────────────────────────
    // ── Redirect ke halaman form peminjaman mandiri ─────────────────────
    public function redirectToForm($itemId)
    {
        return redirect()->route('pages.peminjaman.form', ['item' => $itemId]);
    }

    public function closeDrawer()
    {
        $this->selectedItem = null;
        $this->resetErrorBag();
        $this->dispatch('close-booking-drawer');
    }

    // ── Jam ────────────────────────────────────────────────────────────
    public function setTanggal($date)
    {
        $this->tanggal_peminjaman = $date;
        $this->tanggal_pengembalian = $date;
        $this->checkPjAssignment();
        $this->checkConflict();
    }

    public function setSesi($key)
    {
        if (!isset($this->sesiJam[$key]) || $this->isSesiDisabled($key)) return;
        $this->sesiDipilih = $key;
        $this->modeJam = 'default';
        $this->jam_mulai  = $this->sesiJam[$key]['mulai'];
        $this->jam_selesai = $this->sesiJam[$key]['selesai'];
        $this->checkConflict();
    }

    public function setModeJam($mode)
    {
        $this->modeJam = $mode;
        if ($mode === 'custom') {
            $this->sesiDipilih = '';
        } else {
            foreach ($this->sesiJam as $key => $_) {
                if (!$this->isSesiDisabled($key)) { $this->setSesi($key); break; }
            }
        }
    }

    public function isSesiDisabled($key)
    {
        if (!$this->selectedItem || empty($this->tanggal_peminjaman)) return false;
        $sesi = $this->sesiJam[$key];
        return Booking::where('item_id', $this->selectedItem['id'])
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
            ->where(fn($q) => $q->where('jam_mulai', '<', $sesi['selesai'])->where('jam_selesai', '>', $sesi['mulai']))
            ->exists();
    }

    public function updatedJamMulai()  { $this->checkConflict(); }
    public function updatedJamSelesai(){ $this->checkConflict(); }
    public function updatedTanggalPeminjaman() { $this->checkPjAssignment(); }

    public function checkConflict()
    {
        $this->conflictMessage = '';
        if (!$this->selectedItem || $this->selectedItem['tipe'] !== 'ruangan') return;
        if (empty($this->jam_mulai) || empty($this->jam_selesai)) return;

        $conflict = Booking::where('item_id', $this->selectedItem['id'])
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
            ->where(fn($q) => $q->where('jam_mulai', '<', $this->jam_selesai)->where('jam_selesai', '>', $this->jam_mulai))
            ->first();

        if ($conflict) {
            $this->conflictMessage = 'Ruangan sudah dibooking jam '
                . substr($conflict->jam_mulai, 0, 5) . '–' . substr($conflict->jam_selesai, 0, 5)
                . ' oleh ' . $conflict->nama_peminjam . '.';
        }
    }

    public function checkPjAssignment()
    {
        if (empty($this->tanggal_peminjaman)) {
            $this->currentPjName = '-'; $this->currentPjId = null; return;
        }
        $carbonDate = Carbon::parse($this->tanggal_peminjaman);
        if ($carbonDate->isSunday()) {
            $this->currentPjName = 'UPT Tutup (Minggu)'; $this->currentPjId = null; return;
        }
        $dayMap = ['monday'=>'senin','tuesday'=>'selasa','wednesday'=>'rabu','thursday'=>'kamis','friday'=>'jumat','saturday'=>'sabtu'];
        $dayName = $dayMap[strtolower($carbonDate->format('l'))] ?? 'senin';
        $assignment = DailyAssignment::with('user')->where('hari', $dayName)->first();
        if ($assignment && $assignment->user) {
            $this->currentPjName = $assignment->user->name;
            $this->currentPjId   = $assignment->user->id;
        } else {
            $this->currentPjName = 'Belum ditentukan';
            $this->currentPjId   = null;
        }
    }

    // ── Submit ─────────────────────────────────────────────────────────
    public function save()
    {
        if (!$this->selectedItem) return;
        $isRuangan = $this->selectedItem['tipe'] === 'ruangan';

        $rules = [
            'nama_peminjam'     => 'required|string|max:255',
            'instansi_peminjam' => 'required|string|max:255',
            'no_wa'             => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'bukti_peminjam'    => 'required|image|max:2048',
            'tanggal_peminjaman'=> 'required|date|after_or_equal:today',
            'catatan'           => 'nullable|string|max:1000',
        ];

        if ($isRuangan) {
            $rules['jam_mulai']    = 'required';
            $rules['jam_selesai']  = 'required';
            $rules['jumlah_kursi'] = 'required|integer|min:1|max:' . ($this->maxKapasitasKursi ?: 999);
        } else {
            $rules['tanggal_pengembalian'] = 'required|date|after_or_equal:tanggal_peminjaman';
        }

        $this->validate($rules, [
            'bukti_peminjam.required'  => 'Silakan unggah foto KTM/KTP.',
            'jumlah_kursi.max'         => 'Melebihi kapasitas (' . $this->maxKapasitasKursi . ' kursi).',
        ]);

        if ($isRuangan && $this->jam_selesai <= $this->jam_mulai) {
            $this->addError('jam_selesai', 'Jam selesai harus lebih besar dari jam mulai.');
            return;
        }

        if (empty($this->currentPjId)) {
            session()->flash('error', 'Tidak ada PJ bertugas pada hari tersebut. Hubungi admin.');
            return;
        }

        // Race condition guard ruangan
        if ($isRuangan) {
            $conflict = Booking::where('item_id', $this->selectedItem['id'])
                ->whereIn('status', ['pending', 'disetujui'])
                ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
                ->where(fn($q) => $q->where('jam_mulai', '<', $this->jam_selesai)->where('jam_selesai', '>', $this->jam_mulai))
                ->first();
            if ($conflict) {
                session()->flash('error', 'Ruangan sudah dibooking jam ' . substr($conflict->jam_mulai,0,5) . '–' . substr($conflict->jam_selesai,0,5) . '. Pilih jam lain.');
                return;
            }
        }

        $path = $this->bukti_peminjam->store('proofs', 'public');

        $booking = Booking::create([
            'item_id'             => $this->selectedItem['id'],
            'user_id'             => Auth::id(),
            'penanggung_jawab_id' => $this->currentPjId,
            'nama_peminjam'       => $this->nama_peminjam,
            'instansi_peminjam'   => $this->instansi_peminjam,
            'no_wa'               => $this->no_wa,
            'bukti_peminjam'      => $path,
            'tanggal_peminjaman'  => $this->tanggal_peminjaman,
            'tanggal_pengembalian'=> $isRuangan ? $this->tanggal_peminjaman : $this->tanggal_pengembalian,
            'jam_mulai'           => $isRuangan ? $this->jam_mulai  : null,
            'jam_selesai'         => $isRuangan ? $this->jam_selesai : null,
            'jumlah_kursi'        => $isRuangan ? $this->jumlah_kursi : 0,
            'status'              => 'pending',
            'catatan'             => $this->catatan,
        ]);

        $booking->load('item', 'penanggungJawab');
        $whatsapp = app(WhatsAppService::class);
        $payload = [
            'booking_id'           => $booking->id,
            'nama_peminjam'        => $this->nama_peminjam,
            'instansi_peminjam'    => $this->instansi_peminjam,
            'no_wa'                => $this->no_wa,
            'nama_item'            => $booking->item->nama,
            'kategori_item'        => $booking->item->kategori,
            'tipe_item'            => $booking->item->tipe,
            'tanggal_peminjaman'   => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($booking->tanggal_pengembalian)->translatedFormat('d F Y'),
            'jam_mulai'            => $isRuangan ? $this->jam_mulai : null,
            'jam_selesai'          => $isRuangan ? $this->jam_selesai : null,
            'catatan'              => $this->catatan,
        ];
        $whatsapp->notifyPj(array_merge($payload, [
            'pj_name'   => $booking->penanggungJawab?->name ?? 'PJ Bertugas',
            'pj_no_wa'  => $booking->penanggungJawab?->no_wa ?? '',
        ]));
        $whatsapp->notifyPeminjam($payload);

        if ($isRuangan) {
            Item::find($this->selectedItem['id'])->update(['status' => 'dipinjam']);
        }

        $this->closeDrawer();
        $this->loadItems();
        session()->flash('success', 'Permohonan peminjaman ' . $this->selectedItem['nama'] . ' berhasil dikirim!');
    }
};
?>

{{-- ============================================================ --}}
{{--  HALAMAN INVENTARIS + BOOKING DRAWER                         --}}
{{-- ============================================================ --}}
<div class="relative" 
     x-data="{ drawer: false }" 
     x-on:open-booking-drawer.window="drawer = true"
     x-on:close-booking-drawer.window="drawer = false">

    {{-- ── FLASH MESSAGES ────────────────────────────── --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-4 right-4 z-[200] flex items-center gap-3 bg-emerald-600 text-white text-sm font-semibold px-5 py-3.5 rounded-2xl shadow-xl max-w-sm">
            <i class="fas fa-check-circle text-lg shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100"><i class="fas fa-times text-xs"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="fixed top-4 right-4 z-[200] flex items-center gap-3 bg-red-600 text-white text-sm font-semibold px-5 py-3.5 rounded-2xl shadow-xl max-w-sm">
            <i class="fas fa-exclamation-circle text-lg shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100"><i class="fas fa-times text-xs"></i></button>
        </div>
    @endif

    {{-- ── HEADER + FILTER ────────────────────────────── --}}
    <div class="bg-white border-b border-gray-100 sticky top-[57px] z-30 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-3">

            {{-- Filter Kategori --}}
            <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                @foreach(['' => 'Semua', 'studio' => 'Studio', 'laboratorium' => 'Laboratorium'] as $val => $label)
                    <button wire:click="$set('filterKategori', '{{ $val }}')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all
                               {{ $filterKategori === $val ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>

            {{-- Filter Tipe --}}
            <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl p-1">
                @foreach(['' => 'Semua Tipe', 'peralatan' => '⚙️ Peralatan', 'ruangan' => '🏢 Ruangan'] as $val => $label)
                    <button wire:click="$set('filterTipe', '{{ $val }}')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all
                               {{ $filterTipe === $val ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="ml-auto text-xs text-slate-400 font-medium">
                {{ count($allItems) }} item ditemukan
            </div>
        </div>
    </div>

    {{-- ── ITEM GRID ───────────────────────────────────── --}}
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if(count($allItems) === 0)
            <div class="flex flex-col items-center justify-center py-24 text-slate-400 text-center">
                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex items-center justify-center mb-5">
                    <i class="fas fa-box-open text-3xl opacity-50"></i>
                </div>
                <p class="font-bold text-slate-600 text-base">Tidak ada item ditemukan</p>
                <p class="text-sm mt-1">Coba ubah filter di atas.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($allItems as $item)
                    @php
                        $gambarUrl = $item['gambar'] ? asset('storage/' . $item['gambar']) : null;
                        $isRuangan = $item['tipe'] === 'ruangan';
                        $isTersedia = $item['status'] === 'tersedia';
                        $bgGradient = $isRuangan
                            ? 'from-teal-900 via-teal-800 to-teal-950'
                            : 'from-slate-800 via-slate-700 to-slate-900';
                    @endphp

                    <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                        {{-- Gambar / Thumbnail --}}
                        <div class="relative h-44 bg-gradient-to-br {{ $bgGradient }} overflow-hidden">
                            @if($gambarUrl)
                                <img src="{{ $gambarUrl }}" alt="{{ $item['nama'] }}"
                                     class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center gap-2 opacity-30">
                                    <i class="fas {{ $isRuangan ? 'fa-door-open' : 'fa-tools' }} text-white text-4xl"></i>
                                </div>
                            @endif

                            {{-- Overlay gradient bawah --}}
                            <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>

                            {{-- Badge Kategori --}}
                            <span class="absolute top-3 left-3 text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg
                                         {{ $isRuangan ? 'bg-teal-500/90 text-white' : 'bg-slate-600/90 text-white' }} backdrop-blur-sm">
                                {{ $item['kategori'] === 'studio' ? 'Studio' : 'Lab' }}
                            </span>

                            {{-- Badge Status --}}
                            <span class="absolute top-3 right-3 text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg backdrop-blur-sm
                                         {{ $item['sesiBisaDipinjam'] ? 'bg-emerald-500/90 text-white' : 'bg-red-500/90 text-white' }}">
                                @if($isRuangan)
                                    {{ $item['sesiBisaDipinjam'] ? '✓ ' . $item['sesiTersisaCount'] . ' Sesi Ada' : '✗ Penuh' }}
                                @else
                                    {{ $item['sesiBisaDipinjam'] ? '✓ Tersedia' : '✗ Habis' }}
                                @endif
                            </span>

                            {{-- Badge Tipe (bottom left) --}}
                            <span class="absolute bottom-3 left-3 text-[9px] font-bold text-white/80 flex items-center gap-1.5">
                                <i class="fas {{ $isRuangan ? 'fa-door-open' : 'fa-wrench' }} text-[10px]"></i>
                                {{ $isRuangan ? 'Ruangan' : 'Peralatan' }}
                            </span>
                        </div>

                        {{-- Konten Kartu --}}
                        <div class="p-4 flex-1 flex flex-col justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-teal-700 transition-colors">
                                    {{ $item['nama'] }}
                                </h3>
                                @if($item['deskripsi'])
                                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed line-clamp-2">{{ $item['deskripsi'] }}</p>
                                @endif
                            </div>

                            {{-- Meta info --}}
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-semibold border-t border-slate-50 pt-2.5">
                                @if($isRuangan)
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-chair text-teal-500"></i>
                                        {{ $item['kapasitas_kursi'] }} kursi
                                    </span>
                                @else
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-layer-group text-teal-500"></i>
                                        Stok: {{ $item['stok'] }} unit
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-university text-slate-300"></i>
                                    STAIMAS
                                </span>
                            </div>

                            {{-- Tombol --}}
                            @if($item['sesiBisaDipinjam'])
                                <button wire:click="redirectToForm({{ $item['id'] }})"
                                    class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition-all
                                           bg-teal-700 hover:bg-teal-800 shadow-sm hover:shadow-md hover:shadow-teal-700/20
                                           flex items-center justify-center gap-2">
                                    <i class="fas fa-clipboard-list"></i>
                                    Ajukan Peminjaman
                                </button>
                            @else
                                <div class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-100 text-center cursor-not-allowed select-none">
                                    <i class="fas fa-lock mr-1.5"></i> {{ $isRuangan ? 'Semua Sesi Penuh' : 'Stok Habis' }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- DRAWER OVERLAY --}}
    {{-- ============================================================ --}}
    <div x-show="drawer"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="$wire.closeDrawer()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100]"
         style="display:none">
    </div>

    {{-- DRAWER PANEL --}}
    <div x-show="drawer"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-full max-w-lg bg-white z-[110] shadow-2xl flex flex-col overflow-hidden"
         style="display:none">

        @if($selectedItem)

        {{-- Drawer Header --}}
        <div class="relative h-36 bg-gradient-to-br {{ $selectedItem['tipe'] === 'ruangan' ? 'from-teal-900 to-teal-700' : 'from-slate-900 to-slate-700' }} shrink-0">
            @if($selectedItem['gambar'])
                <img src="{{ asset('storage/' . $selectedItem['gambar']) }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-40">
            @endif
            <div class="absolute inset-0 flex flex-col justify-end p-5">
                <span class="text-[9px] font-black uppercase tracking-widest text-white/60 mb-1">
                    {{ $selectedItem['tipe'] === 'ruangan' ? '🏢 Ruangan' : '⚙️ Peralatan' }} · {{ ucfirst($selectedItem['kategori']) }}
                </span>
                <h3 class="font-black text-white text-lg leading-tight">{{ $selectedItem['nama'] }}</h3>
            </div>
            <button wire:click="closeDrawer"
                class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors backdrop-blur-sm">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Drawer Body --}}
        <div class="flex-1 overflow-y-auto">
            <form wire:submit.prevent="save" class="p-5 space-y-4">

                {{-- Biodata --}}
                <div class="space-y-3">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data Peminjam</p>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Nama Lengkap *</label>
                            <input type="text" wire:model="nama_peminjam"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-600 transition"
                                placeholder="Nama lengkap peminjam">
                            @error('nama_peminjam') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Instansi / Prodi *</label>
                            <input type="text" wire:model="instansi_peminjam"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-600 transition"
                                placeholder="Mahasiswa / Dosen / Unit Kerja">
                            @error('instansi_peminjam') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">
                                <i class="fab fa-whatsapp text-green-600 mr-0.5"></i> No. WhatsApp *
                            </label>
                            <div class="flex gap-2">
                                <span class="px-3 py-2.5 bg-green-50 border border-gray-200 rounded-xl text-xs font-bold text-green-700 shrink-0">+62</span>
                                <input type="tel" wire:model="no_wa"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition"
                                    placeholder="0812-3456-7890">
                            </div>
                            @error('no_wa') <span class="text-red-500 text-xs mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- Waktu --}}
                @if($selectedItem['tipe'] === 'ruangan')
                    {{-- Pilih Tanggal --}}
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Pilih Tanggal</p>
                        <div class="flex gap-2 overflow-x-auto pb-1" style="scrollbar-width:thin">
                            @foreach($listHari as $hari)
                                <button type="button" wire:click="setTanggal('{{ $hari['date'] }}')"
                                    class="flex-shrink-0 flex flex-col items-center justify-center w-14 py-2.5 rounded-xl border-2 transition-all
                                           {{ $tanggal_peminjaman === $hari['date']
                                              ? 'bg-teal-700 border-teal-700 text-white shadow-md scale-105'
                                              : 'bg-white border-slate-200 text-slate-600 hover:border-teal-400' }}">
                                    <span class="text-[8px] font-black uppercase tracking-widest">{{ $hari['day_name'] }}</span>
                                    <span class="text-xl font-black leading-none mt-0.5">{{ $hari['day_num'] }}</span>
                                    <span class="text-[8px] font-bold opacity-70 mt-0.5">{{ $hari['month_name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Pilih Jam --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pilih Jam</p>
                            <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-[9px] font-bold">
                                <button type="button" wire:click="setModeJam('default')"
                                    class="px-2.5 py-1 rounded-md transition-colors {{ $modeJam === 'default' ? 'bg-teal-700 text-white' : 'text-slate-400 hover:text-slate-700' }}">
                                    Sesi
                                </button>
                                <button type="button" wire:click="setModeJam('custom')"
                                    class="px-2.5 py-1 rounded-md transition-colors {{ $modeJam === 'custom' ? 'bg-teal-700 text-white' : 'text-slate-400 hover:text-slate-700' }}">
                                    Kustom
                                </button>
                            </div>
                        </div>

                        @if($conflictMessage)
                            <div class="bg-red-50 border border-red-100 text-red-600 text-xs p-2.5 rounded-xl mb-2 flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle shrink-0"></i>{{ $conflictMessage }}
                            </div>
                        @endif

                        @if($modeJam === 'default')
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($sesiJam as $key => $sesi)
                                    @php $dis = $this->isSesiDisabled($key); $sel = $sesiDipilih === $key; @endphp
                                    <button type="button"
                                        @if(!$dis) wire:click="setSesi('{{ $key }}')" @endif
                                        class="py-3 rounded-xl border-2 text-center flex flex-col items-center gap-0.5 transition-all text-xs
                                               @if($dis) bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed
                                               @elseif($sel) bg-teal-700 border-teal-700 text-white shadow-md scale-[1.02]
                                               @else bg-white border-slate-200 hover:border-teal-400 text-slate-700 cursor-pointer @endif">
                                        <span class="text-base">{{ $sesi['icon'] }}</span>
                                        <span class="font-black text-[11px]">{{ $sesi['label'] }}</span>
                                        <span class="font-medium text-[9px] opacity-80">{{ $sesi['jam'] }}</span>
                                        @if($dis)
                                            <span class="text-[8px] bg-red-100 text-red-500 px-1.5 py-0.5 rounded-full font-black mt-0.5">PENUH</span>
                                        @elseif($sel)
                                            <span class="text-[8px] bg-white/20 text-white px-1.5 py-0.5 rounded-full font-black mt-0.5">✓ DIPILIH</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-3 p-3.5 bg-teal-50/50 border border-teal-100 rounded-xl">
                                <div>
                                    <label class="text-[9px] font-black text-teal-800 uppercase block mb-1">Jam Mulai</label>
                                    <input type="time" wire:model.live="jam_mulai"
                                        class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                    @error('jam_mulai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="text-[9px] font-black text-teal-800 uppercase block mb-1">Jam Selesai</label>
                                    <input type="time" wire:model.live="jam_selesai"
                                        class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                    @error('jam_selesai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Kursi --}}
                        @if($maxKapasitasKursi > 0)
                            <div class="mt-3 flex items-center gap-3 bg-slate-50 border border-slate-100 p-3 rounded-xl">
                                <i class="fas fa-chair text-teal-600 text-lg shrink-0"></i>
                                <div class="flex-1">
                                    <label class="text-[9px] font-black text-slate-500 uppercase block mb-1">Jumlah Kursi (Max {{ $maxKapasitasKursi }})</label>
                                    <input type="number" wire:model="jumlah_kursi" min="1" max="{{ $maxKapasitasKursi }}"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                    @error('jumlah_kursi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>

                @else
                    {{-- Peralatan: tanggal pinjam & kembali --}}
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Tanggal Peminjaman</p>
                        <div class="grid grid-cols-2 gap-3 p-3.5 bg-teal-50/50 border border-teal-100 rounded-xl">
                            <div>
                                <label class="text-[9px] font-black text-teal-800 uppercase block mb-1">Tanggal Pinjam *</label>
                                <input type="date" wire:model.live="tanggal_peminjaman"
                                    class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                @error('tanggal_peminjaman') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-teal-800 uppercase block mb-1">Tanggal Kembali *</label>
                                <input type="date" wire:model="tanggal_pengembalian"
                                    class="w-full px-3 py-2 rounded-lg border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                                @error('tanggal_pengembalian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-100"></div>

                {{-- Upload KTM/KTP --}}
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Bukti Identitas *</p>
                    <label class="flex items-center gap-3 p-3.5 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-teal-50/40 hover:border-teal-300 cursor-pointer transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center shrink-0 shadow-sm">
                            @if($bukti_peminjam)
                                <i class="fas fa-file-image text-teal-600"></i>
                            @else
                                <i class="fas fa-cloud-upload-alt text-gray-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($bukti_peminjam)
                                <p class="text-xs font-bold text-teal-700 truncate">{{ $bukti_peminjam->getClientOriginalName() }}</p>
                                <p class="text-[10px] text-teal-500">Klik untuk ganti</p>
                            @else
                                <p class="text-xs font-semibold text-gray-600">Upload KTM / KTP</p>
                                <p class="text-[10px] text-gray-400">JPG, PNG — maks 2MB</p>
                            @endif
                        </div>
                        <input type="file" wire:model="bukti_peminjam" class="hidden" accept="image/*">
                    </label>
                    <div wire:loading wire:target="bukti_peminjam" class="text-xs text-teal-700 flex items-center gap-1.5 mt-1.5">
                        <i class="fas fa-spinner fa-spin"></i> Mengunggah...
                    </div>
                    @error('bukti_peminjam') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">Tujuan / Catatan</label>
                    <textarea wire:model="catatan" rows="2"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-600 transition leading-relaxed resize-none"
                        placeholder="Tulis keperluan peminjaman..."></textarea>
                </div>

                {{-- PJ Info --}}
                <div class="bg-teal-900 rounded-xl p-3.5 flex items-center justify-between">
                    <div>
                        <span class="text-[8px] font-black uppercase tracking-widest text-teal-400 block">PJ Bertugas</span>
                        <span class="text-xs font-bold text-white">{{ $currentPjName }}</span>
                    </div>
                    <i class="fas fa-user-shield text-teal-400 text-lg"></i>
                </div>

                {{-- Submit --}}
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm shadow-lg shadow-teal-900/20 transition-all flex items-center justify-center gap-2 disabled:opacity-70">
                    <span wire:loading.remove wire:target="save">
                        <i class="fas fa-paper-plane mr-1.5"></i> Kirim Permohonan
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin mr-1.5"></i> Memproses...
                    </span>
                </button>

            </form>
        </div>

        @endif
    </div>

</div>

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
    public $nama_peminjam;
    public $instansi_peminjam;
    public $bukti_peminjam;
    public $tanggal_peminjaman;
    
    public $jam_mulai;
    public $jam_selesai;
    
    public $no_wa;
    public $catatan;

    public $kategori = 'studio';
    public $selected_item_id = '';
    public $selectedItemData = null; // Data kartu terpilih

    public $currentPjName = '';
    public $currentPjId = null;

    public $availableItems = [];

    public $jumlah_kursi = 1;
    public $maxKapasitasKursi = 0;

    public $conflictMessage = '';

    // Bioskop mode
    public $listHari = [];
    public $modeJam = 'default';
    public $sesiDipilih = '';

    // Step navigation
    public $currentStep = 'sesi'; // 'sesi' | 'form' | 'review'

    public $sesiJam = [
        'sesi1' => ['label' => 'Pagi', 'jam' => '08:00 – 10:00', 'mulai' => '08:00', 'selesai' => '10:00', 'icon' => '🌅'],
        'sesi2' => ['label' => 'Siang', 'jam' => '10:00 – 12:00', 'mulai' => '10:00', 'selesai' => '12:00', 'icon' => '☀️'],
        'sesi3' => ['label' => 'Sore', 'jam' => '13:00 – 15:00', 'mulai' => '13:00', 'selesai' => '15:00', 'icon' => '🌤️'],
        'sesi4' => ['label' => 'Petang', 'jam' => '15:00 – 17:00', 'mulai' => '15:00', 'selesai' => '17:00', 'icon' => '🌆'],
    ];

    public function mount(int $selected_item_id = 0)
    {
        $this->generateListHari();
        
        if (count($this->listHari) > 0) {
            $this->tanggal_peminjaman = $this->listHari[0]['date'];
        } else {
            $this->tanggal_peminjaman = Carbon::today()->format('Y-m-d');
        }
        
        $this->jam_mulai = '08:00';
        $this->jam_selesai = '10:00';
        $this->sesiDipilih = 'sesi1';

        $this->autoRestoreCompletedRooms();
        $this->loadItems();
        $this->checkPjAssignment();

        // Pre-select item jika dikirim dari halaman form
        if ($selected_item_id) {
            $this->selected_item_id = $selected_item_id;
            $item = Item::find($selected_item_id);
            if ($item) {
                $this->kategori = $item->kategori;
                $this->selectedItemData = [
                    'id'              => $item->id,
                    'nama'            => $item->nama,
                    'kategori'        => $item->kategori,
                    'stok'            => $item->stok,
                    'deskripsi'       => $item->deskripsi,
                    'gambar'          => $item->gambar,
                    'status'          => $item->status,
                    'kapasitas_kursi' => $item->kapasitas_kursi ?? 0,
                ];
                $this->maxKapasitasKursi = $item->kapasitas_kursi ?? 0;
                $this->loadItems();
            }
        }
    }

    public function generateListHari()
    {
        $list = [];
        $dayMap = [
            'Monday'    => 'SEN',
            'Tuesday'   => 'SEL',
            'Wednesday' => 'RAB',
            'Thursday'  => 'KAM',
            'Friday'    => 'JUM',
            'Saturday'  => 'SAB',
            'Sunday'    => 'MIN'
        ];

        for ($i = 0; $i < 10; $i++) {
            $date = Carbon::today()->addDays($i);
            
            if ($date->isSunday()) {
                continue;
            }

            $list[] = [
                'date' => $date->format('Y-m-d'),
                'day_num' => $date->format('d'),
                'month_name' => strtoupper($date->translatedFormat('M')),
                'day_name' => $dayMap[$date->format('l')] ?? 'SEN',
            ];

            if (count($list) === 7) {
                break;
            }
        }

        $this->listHari = $list;
    }

    public function setTanggal($date)
    {
        $this->tanggal_peminjaman = $date;
        $this->autoRestoreCompletedRooms();
        $this->loadItems();
        $this->checkPjAssignment();
        $this->checkConflict();
    }

    public function selectItem($id)
    {
        $this->selected_item_id = $id;
        $item = collect($this->availableItems)->firstWhere('id', $id);
        $this->selectedItemData = $item;

        if ($item) {
            $this->maxKapasitasKursi = $item['kapasitas_kursi'];
            $this->jumlah_kursi = 1;
        }

        // Reset sesi ke yang pertama valid
        $hasValidSesi = false;
        foreach ($this->sesiJam as $key => $sesi) {
            if (!$this->isSesiDisabled($key)) {
                $this->setSesi($key);
                $hasValidSesi = true;
                break;
            }
        }
        
        if (!$hasValidSesi) {
            $this->setModeJam('custom');
        }

        $this->checkConflict();
        $this->conflictMessage = '';
    }

    public function setSesi($key)
    {
        if (isset($this->sesiJam[$key])) {
            if ($this->isSesiDisabled($key)) {
                return;
            }
            $this->sesiDipilih = $key;
            $this->modeJam = 'default';
            $this->jam_mulai = $this->sesiJam[$key]['mulai'];
            $this->jam_selesai = $this->sesiJam[$key]['selesai'];
            $this->checkConflict();
        }
    }

    public function setModeJam($mode)
    {
        $this->modeJam = $mode;
        if ($mode === 'custom') {
            $this->sesiDipilih = '';
        } else {
            foreach ($this->sesiJam as $key => $sesi) {
                if (!$this->isSesiDisabled($key)) {
                    $this->setSesi($key);
                    break;
                }
            }
        }
        $this->checkConflict();
    }

    public function isSesiDisabled($key)
    {
        if (empty($this->selected_item_id) || empty($this->tanggal_peminjaman)) {
            return false;
        }

        $sesi = $this->sesiJam[$key];
        
        $conflict = Booking::where('item_id', $this->selected_item_id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
            ->where(function($q) use ($sesi) {
                $q->where('jam_mulai', '<', $sesi['selesai'])
                  ->where('jam_selesai', '>', $sesi['mulai']);
            })
            ->exists();

        return $conflict;
    }

    public function updatedKategori()
    {
        $this->selected_item_id = '';
        $this->selectedItemData = null;
        $this->maxKapasitasKursi = 0;
        $this->jumlah_kursi = 1;
        $this->conflictMessage = '';
        $this->loadItems();
    }

    public function updatedJamMulai()
    {
        $this->checkConflict();
    }

    public function updatedJamSelesai()
    {
        $this->checkConflict();
    }

    public function autoRestoreCompletedRooms()
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        Booking::where('status', 'disetujui')
            ->whereHas('item', fn($q) => $q->where('tipe', 'ruangan')->where('status', 'dipinjam'))
            ->where(function($q) use ($today, $currentTime) {
                $q->where('tanggal_peminjaman', '<', $today)
                ->orWhere(function($q2) use ($today, $currentTime) {
                    $q2->where('tanggal_peminjaman', $today)
                       ->where('jam_selesai', '<=', $currentTime);
                });
            })
            ->each(function($booking) {
                $booking->item->update(['status' => 'tersedia']);
                $booking->update(['status' => 'selesai']);
            });
    }

    public function loadItems()
    {
        $tanggal = $this->tanggal_peminjaman ?: Carbon::today()->format('Y-m-d');
        
        $items = Item::where('kategori', $this->kategori)
            ->where('tipe', 'ruangan')
            ->get();

        $this->availableItems = $items->map(function($item) use ($tanggal) {
            $activeBookings = Booking::where('item_id', $item->id)
                ->whereIn('status', ['pending', 'disetujui'])
                ->where('tanggal_peminjaman', $tanggal)
                ->get();

            // Cek apakah SEMUA sesi sudah penuh
            $bookedSesi = [];
            foreach ($this->sesiJam as $key => $sesi) {
                $conflict = $activeBookings->filter(function($b) use ($sesi) {
                    return $b->jam_mulai < $sesi['selesai'] && $b->jam_selesai > $sesi['mulai'];
                })->isNotEmpty();
                if ($conflict) {
                    $bookedSesi[] = $key;
                }
            }

            $allSesiFull = count($bookedSesi) === count($this->sesiJam);

            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'kapasitas_kursi' => $item->kapasitas_kursi ?? 0,
                'status_item' => $item->status,
                'gambar' => $item->gambar,
                'deskripsi' => $item->deskripsi,
                'allSesiFull' => $allSesiFull,
                'bookedSesiCount' => count($bookedSesi),
            ];
        })->toArray();

        // Reset pilihan jika item terpilih tidak ada di list baru
        if ($this->selected_item_id) {
            $found = collect($this->availableItems)->firstWhere('id', $this->selected_item_id);
            if (!$found) {
                $this->selected_item_id = '';
                $this->selectedItemData = null;
                $this->maxKapasitasKursi = 0;
                $this->jumlah_kursi = 1;
            }
        }
        $this->conflictMessage = '';
    }

    public function checkConflict()
    {
        $this->conflictMessage = '';

        if (empty($this->selected_item_id) || empty($this->tanggal_peminjaman) || empty($this->jam_mulai) || empty($this->jam_selesai)) {
            return;
        }

        $conflict = Booking::where('item_id', $this->selected_item_id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
            ->where(function($q) {
                $q->where('jam_mulai', '<', $this->jam_selesai)
                  ->where('jam_selesai', '>', $this->jam_mulai);
            })
            ->first();

        if ($conflict) {
            $this->conflictMessage = 'Ruangan sudah dibooking pada jam ' 
                . substr($conflict->jam_mulai, 0, 5) . ' – ' . substr($conflict->jam_selesai, 0, 5) 
                . ' oleh ' . $conflict->nama_peminjam . '. Pilih jam/sesi lain.';
        }
    }

    public function checkPjAssignment()
    {
        if (empty($this->tanggal_peminjaman)) {
            $this->currentPjName = 'Tidak ada tanggal dipilih';
            $this->currentPjId = null;
            return;
        }

        $carbonDate = Carbon::parse($this->tanggal_peminjaman);
        
        if ($carbonDate->isSunday()) {
            $this->currentPjName = 'UPT Tutup (Hari Minggu)';
            $this->currentPjId = null;
            return;
        }

        $dayEnglish = strtolower($carbonDate->format('l'));
        $dayMap = [
            'monday'    => 'senin',
            'tuesday'   => 'selasa',
            'wednesday' => 'rabu',
            'thursday'  => 'kamis',
            'friday'    => 'jumat',
            'saturday'  => 'sabtu',
            'sunday'    => 'minggu'
        ];
        $dayName = $dayMap[$dayEnglish] ?? 'senin';

        $assignment = DailyAssignment::with('user')->where('hari', $dayName)->first();

        if ($assignment && $assignment->user) {
            $this->currentPjName = $assignment->user->name;
            $this->currentPjId = $assignment->user->id;
        } else {
            $this->currentPjName = 'Belum ditentukan oleh Admin';
            $this->currentPjId = null;
        }
    }

    public function lanjutKeForm()
    {
        if (!$this->selected_item_id) return;

        // Mode sesi: wajib pilih sesi. Mode custom: wajib isi jam
        if ($this->modeJam === 'default' && !$this->sesiDipilih) return;
        if ($this->modeJam === 'custom' && (!$this->jam_mulai || !$this->jam_selesai)) return;

        $this->currentStep = 'form';
    }

    public function kembaliKeSesi()
    {
        $this->currentStep = 'sesi';
    }

    public function lanjutKeReview()
    {
        $this->validate([
            'nama_peminjam'     => 'required|string|max:255',
            'instansi_peminjam' => 'required|string|max:255',
            'no_wa'             => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'bukti_peminjam'    => 'required|image|max:2048',
        ], [
            'bukti_peminjam.required' => 'Silakan unggah foto KTM/KTP sebagai bukti identitas.',
        ]);
        $this->currentStep = 'review';
    }

    public function kembaliKeForm()
    {
        $this->currentStep = 'form';
    }

    public function save()
    {
        $rules = [
            'nama_peminjam' => 'required|string|max:255',
            'instansi_peminjam' => 'required|string|max:255',
            'no_wa' => 'required|string|regex:/^[0-9\-\+\s]+$/|min:9|max:20',
            'bukti_peminjam' => 'required|image|max:2048',
            'tanggal_peminjaman' => 'required|date|after_or_equal:today',
            'selected_item_id' => 'required|exists:items,id',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'jumlah_kursi' => 'required|integer|min:1|max:' . ($this->maxKapasitasKursi ?: 999),
            'catatan' => 'nullable|string|max:1000',
        ];

        $this->validate($rules, [
            'selected_item_id.required' => 'Silakan pilih ruangan terlebih dahulu.',
            'bukti_peminjam.required' => 'Silakan unggah foto KTM/KTP sebagai bukti identitas.',
            'jumlah_kursi.max' => 'Jumlah kursi melebihi kapasitas ruangan (' . $this->maxKapasitasKursi . ' kursi).',
        ]);

        if ($this->jam_selesai <= $this->jam_mulai) {
            $this->addError('jam_selesai', 'Jam selesai harus lebih besar dari jam mulai.');
            return;
        }

        if (empty($this->currentPjId)) {
            session()->flash('error', 'Tidak ada Penanggung Jawab (PJ) bertugas pada hari tersebut. Hubungi admin.');
            return;
        }

        // Race condition guard
        $conflict = Booking::where('item_id', $this->selected_item_id)
            ->whereIn('status', ['pending', 'disetujui'])
            ->where('tanggal_peminjaman', $this->tanggal_peminjaman)
            ->where(function($q) {
                $q->where('jam_mulai', '<', $this->jam_selesai)
                  ->where('jam_selesai', '>', $this->jam_mulai);
            })
            ->first();

        if ($conflict) {
            session()->flash('error', 
                'Maaf, ruangan ini sudah dibooking pada jam ' 
                . substr($conflict->jam_mulai, 0, 5) . ' – ' . substr($conflict->jam_selesai, 0, 5) 
                . '. Silakan pilih jam lain atau ruangan berbeda.'
            );
            return;
        }

        $path = $this->bukti_peminjam->store('proofs', 'public');

        $booking = Booking::create([
            'item_id' => $this->selected_item_id,
            'user_id' => Auth::id(),
            'penanggung_jawab_id' => $this->currentPjId,
            'nama_peminjam' => $this->nama_peminjam,
            'instansi_peminjam' => $this->instansi_peminjam,
            'no_wa' => $this->no_wa,
            'bukti_peminjam' => $path,
            'tanggal_peminjaman' => $this->tanggal_peminjaman,
            'tanggal_pengembalian' => $this->tanggal_peminjaman,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'jumlah_kursi' => $this->jumlah_kursi,
            'status' => 'pending',
            'catatan' => $this->catatan,
        ]);

        $booking->load('item', 'penanggungJawab');
        $whatsapp = app(WhatsAppService::class);
        $whatsapp->notifyPj([
            'booking_id' => $booking->id,
            'pj_name' => $booking->penanggungJawab?->name ?? 'PJ Bertugas',
            'pj_no_wa' => $booking->penanggungJawab?->no_wa ?? '',
            'nama_peminjam' => $this->nama_peminjam,
            'instansi_peminjam' => $this->instansi_peminjam,
            'no_wa' => $this->no_wa,
            'nama_item' => $booking->item->nama,
            'kategori_item' => $booking->item->kategori,
            'tipe_item' => $booking->item->tipe,
            'tanggal_peminjaman' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('d F Y'),
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'catatan' => $this->catatan . "\n(Permintaan Kursi: " . $this->jumlah_kursi . " kursi)",
        ]);

        $whatsapp->notifyPeminjam([
            'booking_id' => $booking->id,
            'nama_peminjam' => $this->nama_peminjam,
            'no_wa' => $this->no_wa,
            'nama_item' => $booking->item->nama,
            'kategori_item' => $booking->item->kategori,
            'tipe_item' => $booking->item->tipe,
            'tanggal_peminjaman' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('l, d F Y'),
            'tanggal_pengembalian' => Carbon::parse($this->tanggal_peminjaman)->translatedFormat('d F Y'),
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'catatan' => $this->catatan,
        ]);

        $item = Item::find($this->selected_item_id);
        $item->update(['status' => 'dipinjam']);

        $this->reset(['nama_peminjam', 'instansi_peminjam', 'no_wa', 'bukti_peminjam', 'selected_item_id', 'catatan', 'conflictMessage', 'selectedItemData']);
        $this->maxKapasitasKursi = 0;
        $this->jumlah_kursi = 1;
        $this->sesiDipilih = '';
        $this->currentStep = 'sesi';
        $this->tanggal_peminjaman = count($this->listHari) > 0 ? $this->listHari[0]['date'] : Carbon::today()->format('Y-m-d');
        $this->autoRestoreCompletedRooms();
        $this->loadItems();

        // Simpan ringkasan booking ke session untuk ditampilkan di layar sukses dengan QR Code
        session()->flash('booking_sukses', [
            'id'      => $booking->id,
            'kode'    => 'BKG-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
            'nama'    => $booking->nama_peminjam,
            'item'    => $booking->item->nama,
            'tanggal' => Carbon::parse($booking->tanggal_peminjaman)->translatedFormat('d F Y'),
            'jam'     => $this->jam_mulai && $this->jam_selesai
                         ? substr($this->jam_mulai, 0, 5) . ' – ' . substr($this->jam_selesai, 0, 5) . ' WIB'
                         : null,
        ]);

    }
};
?>

<div class="space-y-5">

    {{-- ===== PROGRESS STEPPER ===== --}}
    @php
        $steps = $selectedItemData
            ? [['key'=>'sesi','label'=>'Pilih Sesi'], ['key'=>'form','label'=>'Data Diri'], ['key'=>'review','label'=>'Konfirmasi']]
            : [['key'=>'sesi','label'=>'Tanggal & Sesi'], ['key'=>'form','label'=>'Data Diri'], ['key'=>'review','label'=>'Konfirmasi']];
        $stepOrder = array_column($steps, 'key');
        $currentIdx = array_search($currentStep, $stepOrder);
    @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-4">
        <div class="flex items-center justify-between relative">
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
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Ruangan/Studio</p>
                        <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $bs['item'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Tanggal</p>
                        <p class="font-semibold text-slate-700 text-sm mt-0.5">{{ $bs['tanggal'] }}</p>
                    </div>
                    @if($bs['jam'])
                    <div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Jam Sewa</p>
                        <p class="font-semibold text-slate-700 text-sm mt-0.5">{{ $bs['jam'] }}</p>
                    </div>
                    @endif
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

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm p-4 rounded-2xl flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
            </div>
            <div>
                <p class="font-bold">Terjadi Masalah</p>
                <p class="text-red-700 text-xs mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif


    {{-- ============================================================ --}}
    {{-- STEP 1: PILIH TANGGAL --}}
    {{-- ============================================================ --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-xl bg-teal-700 flex items-center justify-center text-white text-xs font-black">1</div>
            <h4 class="font-bold text-gray-900 text-sm">Pilih Tanggal Peminjaman</h4>
        </div>
        <div class="flex gap-2.5 overflow-x-auto pb-2" style="scrollbar-width:thin; scrollbar-color:#0d9488 #f1f5f9;">
            @foreach($listHari as $hari)
                <button type="button" 
                    wire:click="setTanggal('{{ $hari['date'] }}')"
                    class="flex-shrink-0 flex flex-col items-center justify-center w-[68px] py-3.5 rounded-2xl border-2 transition-all duration-200
                           {{ $tanggal_peminjaman === $hari['date'] 
                              ? 'bg-teal-700 border-teal-700 text-white shadow-lg shadow-teal-700/25 scale-105' 
                              : 'bg-white border-slate-200 text-slate-600 hover:border-teal-400 hover:text-teal-700' }}">
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">{{ $hari['day_name'] }}</span>
                    <span class="text-2xl font-black mt-1 leading-none">{{ $hari['day_num'] }}</span>
                    <span class="text-[9px] font-bold mt-1 opacity-70">{{ $hari['month_name'] }}</span>
                </button>
            @endforeach
        </div>
        {{-- PJ Info --}}
        <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-teal-100 flex items-center justify-center shrink-0">
                <i class="fas fa-user-shield text-teal-700 text-xs"></i>
            </div>
            <div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">PJ Bertugas Hari Ini</span>
                <span class="text-xs font-bold text-slate-800">{{ $currentPjName }}</span>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- STEP 2: PILIH KATEGORI & RUANGAN (hanya jika belum pre-select) --}}
    {{-- ============================================================ --}}
    @if(!$selectedItemData)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-xl bg-teal-700 flex items-center justify-center text-white text-xs font-black">2</div>
            <h4 class="font-bold text-gray-900 text-sm">Pilih Ruangan</h4>
        </div>

        {{-- Filter Kategori --}}
        <div class="flex gap-2 mb-5">
            <button type="button" wire:click="$set('kategori', 'studio')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border-2 transition-all
                       {{ $kategori === 'studio' ? 'bg-teal-700 border-teal-700 text-white shadow-md shadow-teal-700/20' : 'border-slate-200 text-slate-600 hover:border-teal-300' }}">
                <i class="fas fa-broadcast-tower"></i> Studio
            </button>
            <button type="button" wire:click="$set('kategori', 'laboratorium')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border-2 transition-all
                       {{ $kategori === 'laboratorium' ? 'bg-teal-700 border-teal-700 text-white shadow-md shadow-teal-700/20' : 'border-slate-200 text-slate-600 hover:border-teal-300' }}">
                <i class="fas fa-flask"></i> Laboratorium
            </button>
        </div>

        {{-- Item Cards Grid --}}
        @if(count($availableItems) === 0)
            <div class="text-center py-10 text-slate-400">
                <i class="fas fa-door-closed text-4xl mb-3 opacity-40"></i>
                <p class="text-sm font-medium">Tidak ada ruangan {{ $kategori === 'studio' ? 'Studio' : 'Laboratorium' }} tersedia.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($availableItems as $item)
                    @php
                        $isSelected = $selected_item_id == $item['id'];
                        $isFull = $item['allSesiFull'];
                        $gambarUrl = $item['gambar'] ? asset('storage/' . $item['gambar']) : null;
                    @endphp
                    <button type="button"
                        @if(!$isFull) wire:click="selectItem({{ $item['id'] }})" @endif
                        class="relative text-left rounded-2xl border-2 overflow-hidden transition-all duration-200 group
                               {{ $isSelected ? 'border-teal-600 shadow-xl shadow-teal-600/20 scale-[1.02]' : ($isFull ? 'border-slate-200 opacity-60 cursor-not-allowed' : 'border-slate-200 hover:border-teal-400 hover:shadow-lg cursor-pointer') }}">
                        
                        {{-- Gambar --}}
                        <div class="relative h-36 bg-gradient-to-br from-teal-800 to-teal-950 overflow-hidden">
                            @if($gambarUrl)
                                <img src="{{ $gambarUrl }}" alt="{{ $item['nama'] }}"
                                     class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-door-open text-teal-400 text-4xl opacity-50"></i>
                                </div>
                            @endif

                            {{-- Overlay badge sesi --}}
                            <div class="absolute top-2.5 right-2.5">
                                @if($isFull)
                                    <span class="text-[10px] font-black bg-red-500 text-white px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
                                        Penuh
                                    </span>
                                @elseif($item['bookedSesiCount'] > 0)
                                    <span class="text-[10px] font-black bg-amber-400 text-amber-900 px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
                                        {{ $item['bookedSesiCount'] }} Sesi Penuh
                                    </span>
                                @else
                                    <span class="text-[10px] font-black bg-emerald-500 text-white px-2.5 py-1 rounded-full uppercase tracking-wider shadow">
                                        Tersedia
                                    </span>
                                @endif
                            </div>

                            {{-- Selected Check --}}
                            @if($isSelected)
                                <div class="absolute inset-0 bg-teal-700/30 flex items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                        <i class="fas fa-check text-teal-700 text-xl"></i>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-4 bg-white">
                            <p class="font-bold text-slate-900 text-sm leading-snug">{{ $item['nama'] }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                                    <i class="fas fa-chair text-teal-600"></i>
                                    {{ $item['kapasitas_kursi'] }} kursi
                                </span>
                                <span class="flex items-center gap-1 text-[11px] text-slate-500 font-medium capitalize">
                                    <i class="fas fa-tag text-teal-600"></i>
                                    {{ $kategori === 'studio' ? 'Studio' : 'Lab' }}
                                </span>
                            </div>
                            @if($isSelected)
                                <div class="mt-2.5 text-[10px] font-bold text-teal-700 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 flex items-center gap-1.5">
                                    <i class="fas fa-check-circle"></i> Ruangan Dipilih
                                </div>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
        @error('selected_item_id') <p class="text-red-500 text-xs mt-3 flex items-center gap-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</p> @enderror
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP 3 / STEP 2 (pre-select): PILIH JAM --}}
    {{-- ============================================================ --}}
    @if($selected_item_id)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6" wire:key="step-jam-{{ $selected_item_id }}">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-xl bg-teal-700 flex items-center justify-center text-white text-xs font-black">{{ $selectedItemData ? '2' : '3' }}</div>
            <h4 class="font-bold text-gray-900 text-sm">Pilih Jam Peminjaman</h4>
            {{-- Toggle Mode --}}
            <div class="ml-auto inline-flex rounded-xl border border-slate-200 p-0.5 bg-slate-50 text-[10px] font-bold">
                <button type="button" wire:click="setModeJam('default')" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $modeJam === 'default' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Sesi
                </button>
                <button type="button" wire:click="setModeJam('custom')" 
                    class="px-3 py-1.5 rounded-lg transition-colors {{ $modeJam === 'custom' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Kustom
                </button>
            </div>
        </div>

        {{-- Conflict Alert --}}
        @if($conflictMessage)
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs p-3.5 rounded-xl flex items-start gap-2 mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 shrink-0"></i>
                <span>{{ $conflictMessage }}</span>
            </div>
        @endif

        @if($modeJam === 'default')
            {{-- Sesi Cards (Ala Bioskop) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($sesiJam as $key => $sesi)
                    @php
                        $isDisabled = $this->isSesiDisabled($key);
                        $isSelected = $sesiDipilih === $key;
                    @endphp
                    <button type="button" 
                        @if(!$isDisabled) wire:click="setSesi('{{ $key }}')" @endif
                        class="py-4 px-3 rounded-2xl text-center border-2 transition-all flex flex-col items-center justify-center gap-1.5
                               @if($isDisabled) 
                                   bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed
                               @elseif($isSelected)
                                   bg-teal-700 border-teal-700 text-white shadow-lg shadow-teal-700/25 scale-[1.03]
                               @else
                                   bg-white border-slate-200 text-slate-700 hover:border-teal-400 hover:text-teal-700 cursor-pointer
                               @endif">
                        <span class="text-xl">{{ $sesi['icon'] }}</span>
                        <span class="text-[11px] font-black uppercase tracking-wide">{{ $sesi['label'] }}</span>
                        <span class="text-[10px] font-semibold {{ $isSelected ? 'opacity-80' : 'text-slate-500' }}">{{ $sesi['jam'] }}</span>
                        @if($isDisabled)
                            <span class="text-[9px] font-black bg-red-100 text-red-600 px-2 py-0.5 rounded-full uppercase tracking-wide mt-0.5">Penuh</span>
                        @elseif($isSelected)
                            <span class="text-[9px] font-black bg-white/20 text-white px-2 py-0.5 rounded-full uppercase tracking-wide mt-0.5">Dipilih ✓</span>
                        @else
                            <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full mt-0.5">Tersedia</span>
                        @endif
                    </button>
                @endforeach
            </div>
            <p class="text-[10px] text-slate-400 mt-3 text-center">
                Sesi dengan label <span class="text-red-500 font-semibold">Penuh</span> sudah dibooking di tanggal ini. 
                Gunakan mode <strong>Kustom</strong> jika ingin jam berbeda.
            </p>
        @else
            {{-- Jam Kustom --}}
            <div class="grid grid-cols-2 gap-4 p-4 bg-teal-50/40 border border-teal-100 rounded-2xl">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-teal-900 uppercase tracking-wider block">Jam Mulai</label>
                    <input type="time" wire:model.live="jam_mulai"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('jam_mulai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-teal-900 uppercase tracking-wider block">Jam Selesai</label>
                    <input type="time" wire:model.live="jam_selesai"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-teal-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('jam_selesai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <p class="text-[10px] text-amber-600 mt-2 bg-amber-50 border border-amber-100 p-2.5 rounded-xl">
                <i class="fas fa-info-circle mr-1"></i>
                Jika jam kustom Anda tumpang tindih dengan sesi default yang sudah penuh, permohonan akan ditolak secara otomatis.
            </p>
        @endif

        {{-- Permintaan Kursi --}}
        @if($maxKapasitasKursi > 0)
            <div class="mt-5 p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-4">
                <div class="shrink-0 text-center">
                    <i class="fas fa-chair text-teal-600 text-2xl"></i>
                </div>
                <div class="flex-1">
                    <label class="text-[10px] font-black text-slate-700 uppercase tracking-wider block mb-1.5">
                        Jumlah Kursi — Max {{ $maxKapasitasKursi }} Kursi
                    </label>
                    <input type="number" wire:model="jumlah_kursi" min="1" max="{{ $maxKapasitasKursi }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-teal-700">
                    @error('jumlah_kursi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- TOMBOL LANJUT (di bawah step sesi) --}}
    @if($currentStep === 'sesi' && $selected_item_id && ($sesiDipilih || ($modeJam === 'custom' && $jam_mulai && $jam_selesai)))
    <button type="button" wire:click="lanjutKeForm"
        class="w-full py-3.5 rounded-2xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-sm shadow-lg shadow-teal-900/20 transition-all hover:scale-[1.01] flex items-center justify-center gap-2">
        Lanjut Isi Data Diri <i class="fas fa-arrow-right text-xs"></i>
    </button>
    @endif

    {{-- ============================================================ --}}
    {{-- STEP FORM: ISI DATA PEMINJAM --}}
    {{-- ============================================================ --}}
    @if($currentStep === 'form' && $selected_item_id)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">

        {{-- Summary chip --}}
        <div class="grid grid-cols-2 gap-3 p-4 bg-teal-950 rounded-2xl text-white text-xs">
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Ruangan</p>
                <p class="font-bold mt-0.5 truncate">{{ $selectedItemData['nama'] ?? '-' }}</p>
            </div>
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Tanggal</p>
                <p class="font-bold mt-0.5">{{ $tanggal_peminjaman ? \Carbon\Carbon::parse($tanggal_peminjaman)->translatedFormat('d M Y') : '-' }}</p>
            </div>
            <div>
                <p class="text-teal-400 text-[9px] uppercase font-black tracking-wider">Jam</p>
                <p class="font-bold mt-0.5">{{ $jam_mulai ? substr($jam_mulai,0,5) : '-' }} – {{ $jam_selesai ? substr($jam_selesai,0,5) : '-' }}</p>
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
                    @if($bukti_peminjam && !is_string($bukti_peminjam) && method_exists($bukti_peminjam, 'temporaryUrl'))
                        <img src="{{ $bukti_peminjam->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif($bukti_peminjam)
                        <i class="fas fa-file-image text-teal-600 text-xl"></i>
                    @else
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-xl"></i>
                    @endif
                </div>
                <div class="flex-1">
                    @if($bukti_peminjam)
                        <p class="text-sm font-bold text-teal-700 truncate max-w-[200px]">{{ is_string($bukti_peminjam) ? $bukti_peminjam : $bukti_peminjam->getClientOriginalName() }}</p>
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
                placeholder="Contoh: Untuk rekaman video tugas mata kuliah praktikum microteaching..."></textarea>
        </div>

        {{-- Navigasi --}}
        <div class="flex gap-3 pt-1">
            <button type="button" wire:click="kembaliKeSesi"
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
    {{-- STEP REVIEW: KONFIRMASI SEBELUM KIRIM --}}
    {{-- ============================================================ --}}
    @if($currentStep === 'review' && $selected_item_id)
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-teal-900 to-teal-700 px-6 py-5 text-white">
            <p class="text-[10px] font-black uppercase tracking-widest text-teal-300 mb-1">Konfirmasi Peminjaman Ruangan</p>
            <h4 class="font-extrabold text-lg">Cek kembali pesanan Anda</h4>
            <p class="text-teal-300 text-xs mt-0.5">Pastikan semua data sudah benar sebelum mengirim permohonan.</p>
        </div>

        <div class="p-6 space-y-0">
            @php
                $reviewRows = [
                    ['icon'=>'fa-door-open','label'=>'Ruangan','value'=>$selectedItemData['nama'] ?? '-'],
                    ['icon'=>'fa-calendar','label'=>'Tanggal','value'=> $tanggal_peminjaman ? \Carbon\Carbon::parse($tanggal_peminjaman)->translatedFormat('l, d F Y') : '-'],
                    ['icon'=>'fa-clock','label'=>'Jam','value'=> ($jam_mulai ? substr($jam_mulai,0,5) : '-') . ' – ' . ($jam_selesai ? substr($jam_selesai,0,5) : '-') . ' WIB'],
                    ['icon'=>'fa-chair','label'=>'Jumlah Kursi','value'=> $jumlah_kursi . ' kursi'],
                    ['icon'=>'fa-user','label'=>'Nama Peminjam','value'=>$nama_peminjam ?? '-'],
                    ['icon'=>'fa-university','label'=>'Instansi / Prodi','value'=>$instansi_peminjam ?? '-'],
                    ['icon'=>'fa-phone-alt','label'=>'WhatsApp','value'=>'+62 ' . ($no_wa ?? '-')],
                    ['icon'=>'fa-user-shield','label'=>'PJ Bertugas','value'=>$currentPjName ?: '-'],
                    ['icon'=>'fa-comment-alt','label'=>'Catatan','value'=>$catatan ?: '(tidak ada)'],
                ];
            @endphp
            @foreach($reviewRows as $row)
            <div class="flex items-start gap-4 py-3.5 border-b border-slate-50 last:border-0">
                <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas {{ $row['icon'] }} text-teal-600 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">{{ $row['label'] }}</p>
                    <p class="text-sm font-semibold text-slate-900 mt-0.5 break-words">{{ $row['value'] }}</p>
                </div>
            </div>
            @endforeach
            <div class="flex items-start gap-4 py-3.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-id-card text-teal-600 text-xs"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Bukti KTM/KTP</p>
                    @if($bukti_peminjam)
                        <p class="text-sm font-semibold text-emerald-700 mt-0.5 flex items-center gap-1.5">
                            <i class="fas fa-check-circle text-emerald-500"></i> 
                            {{ is_string($bukti_peminjam) ? $bukti_peminjam : (method_exists($bukti_peminjam, 'getClientOriginalName') ? $bukti_peminjam->getClientOriginalName() : 'Identitas Terunggah') }}
                        </p>
                    @else
                        <p class="text-sm text-red-500 mt-0.5">Belum diupload</p>
                    @endif
                </div>
            </div>
        </div>

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



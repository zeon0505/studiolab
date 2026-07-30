@extends('layouts.app')
@section('title', 'Kalender Ketersediaan')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-teal-600"></i> Kalender Ketersediaan
                </h1>
                <p class="text-sm text-slate-500 mt-1">Lihat tanggal mana yang sudah terisi sebelum mengajukan peminjaman.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('pages.peminjaman.ruangan') }}"
                   class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
                    <i class="fas fa-plus text-xs"></i> Ajukan Peminjaman
                </a>
            </div>
        </div>

        {{-- Tipe Toggle --}}
        <div class="flex gap-2 mt-5">
            @foreach(['ruangan' => ['fas fa-door-open', 'Ruangan & Studio'], 'peralatan' => ['fas fa-tools', 'Peralatan']] as $key => [$icon, $label])
            <a href="{{ route('pages.kalender', ['tipe' => $key, 'bulan' => $bulan]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border-2 transition-all
                      {{ $tipe === $key ? 'bg-teal-700 border-teal-700 text-white shadow-md' : 'border-slate-200 text-slate-600 hover:border-teal-300 bg-white' }}">
                <i class="{{ $icon }} text-xs"></i> {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Calendar Card --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-8">

        {{-- Month Navigation --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <a href="{{ route('pages.kalender', ['tipe' => $tipe, 'bulan' => $prevMonth]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-700 transition-all">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <h2 class="font-black text-slate-900 text-base">
                {{ $startOfMonth->translatedFormat('F Y') }}
            </h2>
            <a href="{{ route('pages.kalender', ['tipe' => $tipe, 'bulan' => $nextMonth]) }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-700 transition-all">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
        </div>

        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-slate-100">
            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $dayName)
            <div class="py-2.5 text-center text-[10px] font-black uppercase tracking-widest
                        {{ $dayName === 'Min' ? 'text-red-400' : ($dayName === 'Sab' ? 'text-blue-400' : 'text-slate-400') }}">
                {{ $dayName }}
            </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        @php
            $firstDayOfWeek = $startOfMonth->copy()->dayOfWeek; // 0=Sun, 6=Sat
            $daysInMonth    = $endOfMonth->day;
            $today          = \Carbon\Carbon::today()->format('Y-m-d');
        @endphp
        <div class="grid grid-cols-7">

            {{-- Empty cells sebelum hari 1 --}}
            @for ($i = 0; $i < $firstDayOfWeek; $i++)
            <div class="h-24 border-b border-r border-slate-50 bg-slate-50/50"></div>
            @endfor

            {{-- Days --}}
            @for ($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateStr    = $startOfMonth->copy()->setDay($day)->format('Y-m-d');
                    $dayOfWeek  = \Carbon\Carbon::parse($dateStr)->dayOfWeek; // 0=Sun
                    $bookingList= $calendarData[$dateStr] ?? [];
                    $bookingCount = count($bookingList);
                    $isToday    = $dateStr === $today;
                    $isPast     = $dateStr < $today;
                    $isSunday   = $dayOfWeek === 0;
                    $isLastCol  = $dayOfWeek === 6; // Saturday

                    // Determine load level
                    $loadClass = '';
                    if ($bookingCount === 0)        $loadClass = 'free';
                    elseif ($bookingCount <= 2)    $loadClass = 'partial';
                    else                            $loadClass = 'full';
                @endphp
                <div class="h-24 border-b {{ !$isLastCol ? 'border-r' : '' }} border-slate-100 p-1.5 relative
                            {{ $isPast ? 'bg-slate-50' : '' }}
                            {{ $isSunday ? 'bg-red-50/30' : '' }}
                            {{ $isToday ? 'ring-2 ring-inset ring-teal-500' : '' }}">

                    {{-- Day number --}}
                    <div class="mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 text-[11px] font-black rounded-full
                                     {{ $isToday ? 'bg-teal-600 text-white' : ($isSunday ? 'text-red-400' : ($isPast ? 'text-slate-300' : 'text-slate-700')) }}">
                            {{ $day }}
                        </span>
                    </div>

                    {{-- Booking dots --}}
                    @if($bookingCount > 0 && !$isPast)
                        @foreach($bookingList->take(2) as $b)
                        <div class="text-[8px] font-bold px-1.5 py-0.5 rounded-md mb-0.5 truncate leading-tight
                                    {{ $b->status === 'disetujui' ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-700' }}">
                            {{ $b->item->nama }}
                        </div>
                        @endforeach
                        @if($bookingCount > 2)
                        <div class="text-[8px] font-bold text-slate-400 px-1">+{{ $bookingCount - 2 }} lagi</div>
                        @endif
                    @endif

                    {{-- Free indicator --}}
                    @if($bookingCount === 0 && !$isPast && !$isSunday)
                    <div class="absolute bottom-1 right-1.5">
                        <span class="w-2 h-2 bg-emerald-300 rounded-full block"></span>
                    </div>
                    @endif
                </div>
            @endfor

            {{-- Trailing empty cells --}}
            @php
                $lastDayOfWeek = $endOfMonth->copy()->dayOfWeek;
                $trailingCells = $lastDayOfWeek < 6 ? 6 - $lastDayOfWeek : 0;
            @endphp
            @for ($i = 0; $i < $trailingCells; $i++)
            <div class="h-24 border-b {{ $i < $trailingCells - 1 ? 'border-r' : '' }} border-slate-50 bg-slate-50/50"></div>
            @endfor

        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Keterangan:</span>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
            <span class="w-3 h-3 bg-emerald-300 rounded-full"></span> Bebas
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
            <span class="w-3 h-3 bg-amber-100 border border-amber-300 rounded-sm"></span> Ada booking menunggu
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
            <span class="w-3 h-3 bg-teal-100 border border-teal-300 rounded-sm"></span> Sudah disetujui
        </div>
        <div class="flex items-center gap-1.5 text-xs text-slate-600">
            <span class="w-3 h-3 bg-slate-100 border border-slate-200 rounded-full"></span> Hari ini
        </div>
    </div>

    {{-- Item List --}}
    @if($items->count() > 0)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <h3 class="font-black text-slate-900 text-sm mb-4 flex items-center gap-2">
            <i class="{{ $tipe === 'ruangan' ? 'fas fa-door-open' : 'fas fa-tools' }} text-teal-600"></i>
            {{ $tipe === 'ruangan' ? 'Ruangan & Studio Tersedia' : 'Peralatan Tersedia' }}
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($items as $item)
            <a href="{{ route($tipe === 'ruangan' ? 'pages.peminjaman.ruangan' : 'pages.peminjaman.peralatan') }}"
               class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-100 hover:border-teal-300 hover:bg-teal-50 transition-all group">
                <div class="w-10 h-10 rounded-xl {{ $tipe === 'ruangan' ? 'bg-blue-100' : 'bg-teal-100' }} flex items-center justify-center shrink-0">
                    @if($item->gambar)
                    <img src="{{ asset('storage/'.$item->gambar) }}" alt="{{ $item->nama }}" class="w-full h-full object-cover rounded-xl">
                    @else
                    <i class="{{ $tipe === 'ruangan' ? 'fas fa-door-open text-blue-500' : 'fas fa-tools text-teal-500' }} text-sm"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate group-hover:text-teal-700 transition-colors">{{ $item->nama }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 capitalize">{{ $item->kategori }}
                        @if($item->stok > 1) · {{ $item->stok }} unit @endif
                    </p>
                </div>
                <i class="fas fa-arrow-right text-slate-300 text-xs group-hover:text-teal-500 transition-colors"></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

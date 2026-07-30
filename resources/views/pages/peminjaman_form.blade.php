@extends('layouts.app')
@section('title', 'Ajukan Peminjaman — ' . $item->nama)

@section('content')

{{-- Hero Banner Item --}}
<div class="relative h-56 sm:h-64 overflow-hidden bg-gradient-to-br {{ $item->tipe === 'ruangan' ? 'from-teal-950 via-teal-900 to-teal-800' : 'from-slate-900 via-slate-800 to-slate-700' }}">
    @if($item->gambar)
        <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama }}"
             class="absolute inset-0 w-full h-full object-cover opacity-30">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>

    {{-- Back link --}}
    <div class="absolute top-4 left-4">
        <a href="{{ route('pages.peralatan') }}"
           class="inline-flex items-center gap-2 text-white/80 hover:text-white text-xs font-semibold bg-white/10 hover:bg-white/20 backdrop-blur-sm px-3.5 py-2 rounded-xl transition">
            <i class="fas fa-arrow-left text-[10px]"></i> Inventaris
        </a>
    </div>

    {{-- Item info di bawah banner --}}
    <div class="absolute bottom-5 left-5 right-5">
        <div class="flex items-end gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center border border-white/20 shrink-0">
                <i class="fas {{ $item->tipe === 'ruangan' ? 'fa-door-open' : 'fa-tools' }} text-white text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black uppercase tracking-widest bg-teal-500/80 text-white px-2 py-0.5 rounded-md backdrop-blur-sm">{{ ucfirst($item->kategori) }}</span>
                    <span class="text-[9px] font-black uppercase tracking-widest bg-white/10 text-white/80 px-2 py-0.5 rounded-md">{{ ucfirst($item->tipe) }}</span>
                </div>
                <h1 class="text-white font-extrabold text-lg leading-tight">{{ $item->nama }}</h1>
                @if($item->deskripsi)
                    <p class="text-white/60 text-xs mt-0.5 line-clamp-1">{{ $item->deskripsi }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Form --}}
<div class="max-w-2xl mx-auto px-4 py-8">
    @if($item->tipe === 'ruangan')
        @livewire('⚡booking-ruangan', ['selected_item_id' => $item->id])
    @else
        @livewire('⚡booking-peralatan', ['selected_item_id' => $item->id])
    @endif
</div>

@endsection

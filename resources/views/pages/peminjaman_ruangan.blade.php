@extends('layouts.app')
@section('title', 'Peminjaman Ruangan Studio & Lab')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-teal-600 text-xs font-bold uppercase tracking-widest mb-3">
            <i class="fas fa-door-open"></i>
            <span>Peminjaman Ruangan</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">Reservasi Studio & Laboratorium</h1>
        <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
            Pilih ruangan yang tersedia, tentukan tanggal & jam, lalu isi data peminjam. Permohonan akan diverifikasi oleh admin UPT.
        </p>

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 mt-4 text-[11px] text-slate-400 font-medium">
            <a href="{{ route('home') }}" class="hover:text-teal-700 transition">Beranda</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            <span class="text-slate-600 font-semibold">Peminjaman Ruangan</span>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fas fa-info-circle text-blue-500 text-sm"></i>
        </div>
        <div class="text-xs text-blue-800 leading-relaxed">
            <p class="font-bold mb-0.5">Informasi Penting</p>
            Peminjaman ruangan bersifat per-sesi jam. Setiap ruangan dapat dipinjam oleh <strong>satu peminjam per sesi</strong>. 
            Sesi yang sudah penuh akan ditampilkan dengan label <span class="text-red-600 font-semibold">Penuh</span> dan tidak bisa dipilih.
        </div>
    </div>

    @livewire('⚡booking-ruangan')
</div>
@endsection

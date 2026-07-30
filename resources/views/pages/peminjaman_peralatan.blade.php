@extends('layouts.app')
@section('title', 'Peminjaman Peralatan UPT')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-teal-600 text-xs font-bold uppercase tracking-widest mb-3">
            <i class="fas fa-tools"></i>
            <span>Peminjaman Peralatan</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">Peminjaman Inventaris Peralatan</h1>
        <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
            Pilih peralatan yang ingin dipinjam, isi data peminjam dan tanggal peminjaman. Permohonan akan diverifikasi oleh admin UPT.
        </p>

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 mt-4 text-[11px] text-slate-400 font-medium">
            <a href="{{ route('home') }}" class="hover:text-teal-700 transition">Beranda</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            <span class="text-slate-600 font-semibold">Peminjaman Peralatan</span>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="mb-6 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fas fa-clipboard-list text-amber-500 text-sm"></i>
        </div>
        <div class="text-xs text-amber-800 leading-relaxed">
            <p class="font-bold mb-0.5">Informasi Penting</p>
            Peralatan yang dipinjam harus dikembalikan sebelum atau pada tanggal yang ditentukan. 
            Wajib menyertakan <strong>bukti identitas (KTM/KTP)</strong> sebagai jaminan peminjaman.
        </div>
    </div>

    @livewire('⚡booking-peralatan')
</div>
@endsection

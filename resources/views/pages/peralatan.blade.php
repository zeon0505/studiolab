@extends('layouts.app')
@section('title', 'Inventaris Peralatan & Ruangan')

@section('content')
<div class="max-w-6xl mx-auto px-4 pt-8 pb-4">
    <h1 class="text-2xl font-extrabold text-slate-900">Inventaris Peralatan & Ruangan</h1>
    <p class="text-sm text-slate-500 mt-1">Klik <span class="font-semibold text-teal-700">Ajukan Peminjaman</span> pada item yang ingin dipinjam.</p>
</div>

@livewire('⚡inventaris')
@endsection

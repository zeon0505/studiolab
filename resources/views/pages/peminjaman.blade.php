@extends('layouts.app')
@section('title', 'Form Pengajuan Peminjaman')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  {{-- Header Halaman --}}
  <div class="text-center pb-2">
    <h2 class="text-2xl font-extrabold text-gray-900">Form Pengajuan Online</h2>
    <p class="text-xs text-gray-500 mt-1">Layanan peminjaman UPT Studio Penyiaran & Laboratorium Terpadu STAIMAS Wonogiri.</p>
  </div>

  {{-- Render Livewire Component Form Peminjaman --}}
  @livewire('booking-form')

</div>
@endsection

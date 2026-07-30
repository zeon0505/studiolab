@extends('layouts.app')
@section('title', 'Struktur Pengelola')

@section('content')
<div class="max-w-4xl mx-auto space-y-12">

  {{-- Header Halaman --}}
  <div class="border-b border-gray-100 pb-5 text-center">
    <h2 class="text-2xl font-extrabold text-gray-900">Struktur Pengelola Studio & Laboratorium</h2>
    <p class="text-xs text-gray-500 mt-1">Tim Pengelola Unit Pelaksana Teknis (UPT) Studio Penyiaran & Laboratorium Terpadu STAIMAS Wonogiri.</p>
  </div>

  {{-- Struktur Bagan Organisasi Minimalis --}}
  <div class="space-y-10">
    
    {{-- Kepala UPT --}}
    <div class="flex flex-col items-center">
      <div class="bg-white rounded-3xl border border-gray-150 p-5 shadow-sm w-full max-w-xs text-center relative hover:shadow-md transition-all">
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-teal-700 text-white text-[9px] font-bold px-3 py-0.5 rounded-full uppercase">
          Kepala UPT
        </div>
        <div class="w-14 h-14 bg-teal-50 rounded-2xl mx-auto flex items-center justify-center text-teal-700 text-xl mb-3 shadow-inner">
          <i class="fas fa-user-tie"></i>
        </div>
        <h4 class="font-extrabold text-sm text-gray-900">Indra Fauzi, M.Kom.</h4>
        <p class="text-[10px] text-gray-400 mt-0.5">NIDN. 0622118801</p>
      </div>
    </div>

    {{-- Line penghubung vertical --}}
    <div class="w-0.5 h-8 bg-teal-200 mx-auto"></div>

    {{-- Pengelola Cabang --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
      
      {{-- Line horizontal penghubung --}}
      <div class="hidden md:block absolute top-0 left-1/4 right-1/4 h-0.5 bg-teal-200"></div>
      
      {{-- Kepala Studio --}}
      <div class="flex flex-col items-center relative pt-4">
        <div class="hidden md:block absolute top-0 left-1/2 -translate-x-1/2 w-0.5 h-4 bg-teal-200"></div>
        <div class="bg-white rounded-3xl border border-gray-150 p-5 shadow-sm w-full max-w-xs text-center relative hover:shadow-md transition-all">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-teal-600 text-white text-[9px] font-bold px-3 py-0.5 rounded-full uppercase">
            Kepala Studio Penyiaran
          </div>
          <div class="w-14 h-14 bg-teal-50 rounded-2xl mx-auto flex items-center justify-center text-teal-600 text-xl mb-3 shadow-inner">
            <i class="fas fa-microphone-alt"></i>
          </div>
          <h4 class="font-extrabold text-sm text-gray-900">Rina Asih, M.Sos.</h4>
          <p class="text-[10px] text-gray-400 mt-0.5">NIDN. 0601058702</p>
        </div>
      </div>

      {{-- Kepala Lab --}}
      <div class="flex flex-col items-center relative pt-4">
        <div class="hidden md:block absolute top-0 left-1/2 -translate-x-1/2 w-0.5 h-4 bg-teal-200"></div>
        <div class="bg-white rounded-3xl border border-gray-150 p-5 shadow-sm w-full max-w-xs text-center relative hover:shadow-md transition-all">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-teal-600 text-white text-[9px] font-bold px-3 py-0.5 rounded-full uppercase">
            Kepala Laboratorium Terpadu
          </div>
          <div class="w-14 h-14 bg-teal-50 rounded-2xl mx-auto flex items-center justify-center text-teal-600 text-xl mb-3 shadow-inner">
            <i class="fas fa-desktop"></i>
          </div>
          <h4 class="font-extrabold text-sm text-gray-900">Yulianto, M.Pd.</h4>
          <p class="text-[10px] text-gray-400 mt-0.5">NIDN. 0614088503</p>
        </div>
      </div>

    </div>

    {{-- Line penghubung staff --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="w-0.5 h-8 bg-teal-200 mx-auto hidden md:block"></div>
      <div class="w-0.5 h-8 bg-teal-200 mx-auto hidden md:block"></div>
    </div>

    {{-- Staf Pelaksana / Teknisi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      {{-- Staff Studio --}}
      <div class="flex flex-col items-center">
        <div class="bg-white rounded-3xl border border-gray-150 p-5 shadow-sm w-full max-w-xs text-center relative hover:shadow-md transition-all">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-teal-500 text-white text-[9px] font-bold px-3 py-0.5 rounded-full uppercase">
            Teknisi Studio
          </div>
          <div class="w-14 h-14 bg-teal-50 rounded-2xl mx-auto flex items-center justify-center text-teal-500 text-xl mb-3 shadow-inner">
            <i class="fas fa-cogs"></i>
          </div>
          <h4 class="font-extrabold text-sm text-gray-900">Dicky Kurniawan, A.Md.</h4>
          <p class="text-[10px] text-gray-400 mt-0.5">NIP. 19951012202301</p>
        </div>
      </div>

      {{-- Staff Lab --}}
      <div class="flex flex-col items-center">
        <div class="bg-white rounded-3xl border border-gray-150 p-5 shadow-sm w-full max-w-xs text-center relative hover:shadow-md transition-all">
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-teal-500 text-white text-[9px] font-bold px-3 py-0.5 rounded-full uppercase">
            Laboran & IT Support
          </div>
          <div class="w-14 h-14 bg-teal-50 rounded-2xl mx-auto flex items-center justify-center text-teal-500 text-xl mb-3 shadow-inner">
            <i class="fas fa-wrench"></i>
          </div>
          <h4 class="font-extrabold text-sm text-gray-900">Luthfi Aziz, S.Kom.</h4>
          <p class="text-[10px] text-gray-400 mt-0.5">NIP. 19970824202302</p>
        </div>
      </div>

    </div>

  </div>

</div>
@endsection

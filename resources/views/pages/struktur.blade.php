@extends('layouts.app')
@section('title', 'Struktur Pengelola')

@section('content')
<div class="max-w-4xl mx-auto space-y-12">

  {{-- Header Halaman --}}
  <div class="border-b border-gray-100 pb-5 text-center">
    <h2 class="text-2xl font-extrabold text-gray-900">Struktur Pengelola Studio & Laboratorium</h2>
    <p class="text-xs text-gray-500 mt-1">Unit Pelaksana Teknis (UPT) Studio Penyiaran & Laboratorium Terpadu STAIMAS Wonogiri.</p>
  </div>

  {{-- Struktur Bagan Organisasi Minimalis --}}
  <div class="flex flex-col items-center py-6">
    
    {{-- Kepala UPT --}}
    <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm w-full max-w-sm text-center relative hover:shadow-md transition-all">
      {{-- Circular Image with Dark Border --}}
      <div style="width: 170px; height: 170px; margin: 0 auto 1.5rem auto; overflow: hidden; border-radius: 50%; border: 3px solid #334155; padding: 3px; background-color: #f9fafb; display: flex; align-items: center; justify-content: center;">
        <img src="{{ asset('pas-photo-1.jpg') }}" alt="MUHAMMAD UMAR KHADAFI, M.Sos" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; object-position: center 20%;">
      </div>
      
      {{-- Name & Title --}}
      <h4 class="font-extrabold text-lg text-slate-900 tracking-tight uppercase">MUHAMMAD UMAR KHADAFI, M.Sos</h4>
      <p class="text-xs text-teal-600 font-bold mt-1">Kepala UPT Studio & Lab</p>

      {{-- Link SK REKTOR --}}
      <div class="mt-6 pt-4 border-t border-gray-100">
        <a href="{{ asset('sk-rektor.pdf') }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-teal-700 hover:text-teal-900 transition-colors">
          <i class="fas fa-file-pdf text-red-500 text-sm"></i>
          <span>Lihat SK Rektor</span>
        </a>
      </div>
    </div>

  </div>

</div>
@endsection

@extends('layouts.app')
@section('title', 'Alur Peminjaman')

@section('content')
<div class="max-w-4xl mx-auto space-y-12">

  {{-- Header Halaman --}}
  <div class="border-b border-gray-100 pb-5 text-center">
    <h2 class="text-2xl font-extrabold text-gray-900">Alur & Syarat Peminjaman</h2>
    <p class="text-xs text-gray-500 mt-1">Pahami tata cara peminjaman inventarisasi studio penyiaran dan laboratorium komputer-bahasa.</p>
  </div>

  {{-- Syarat Peminjaman --}}
  <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-5">
    <div class="flex items-center gap-3 border-b border-gray-50 pb-3">
      <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
        <i class="fas fa-clipboard-list text-sm"></i>
      </div>
      <h3 class="font-bold text-base text-gray-900">Syarat Peminjaman</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="p-4 bg-gray-50 rounded-2xl flex gap-3">
        <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">A</span>
        <div>
          <h4 class="font-bold text-xs text-gray-800">Nama Peminjam</h4>
          <p class="text-[11px] text-gray-400 mt-0.5">Merupakan mahasiswa aktif, dosen, staf, atau tamu resmi STAIMAS Wonogiri.</p>
        </div>
      </div>

      <div class="p-4 bg-gray-50 rounded-2xl flex gap-3">
        <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">B</span>
        <div>
          <h4 class="font-bold text-xs text-gray-800">Instansi Peminjam</h4>
          <p class="text-[11px] text-gray-400 mt-0.5">Harus menyertakan asal organisasi, prodi, atau instansi terkait.</p>
        </div>
      </div>

      <div class="p-4 bg-gray-50 rounded-2xl flex gap-3">
        <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">C</span>
        <div>
          <h4 class="font-bold text-xs text-gray-800">Bukti Identitas (KTM/KTP)</h4>
          <p class="text-[11px] text-gray-400 mt-0.5">Wajib mengunggah foto kartu identitas (KTM/KTP) yang sah pada form online.</p>
        </div>
      </div>

      <div class="p-4 bg-gray-50 rounded-2xl flex gap-3">
        <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">D</span>
        <div>
          <h4 class="font-bold text-xs text-gray-800">Durasi Peminjaman</h4>
          <p class="text-[11px] text-gray-400 mt-0.5">Tanggal peminjaman & pengembalian ditentukan secara tertulis di awal.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Timeline Alur --}}
  <div class="space-y-8">
    <h3 class="font-bold text-base text-gray-900 text-center">Tahapan Peminjaman</h3>
    
    <div class="relative border-l border-teal-200 ml-4 md:ml-32 space-y-8">
      
      {{-- Langkah 1 --}}
      <div class="relative pl-6 sm:pl-8">
        <div class="absolute -left-3.5 top-1.5 w-7 h-7 rounded-full bg-teal-700 border-4 border-white shadow flex items-center justify-center text-white text-[10px] font-bold">
          1
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
          <h4 class="font-bold text-sm text-teal-950">Isi Form Peminjaman Online</h4>
          <p class="text-xs text-gray-500 leading-relaxed">
            Buka menu <a href="{{ route('pages.peminjaman.ruangan') }}" class="text-teal-700 font-bold hover:underline">Form Peminjaman</a>, tentukan layanan (Studio / Laboratorium), jenis peminjaman (Peralatan / Ruangan), lengkapi formulir identitas, lalu unggah foto KTM/KTP.
          </p>
        </div>
      </div>

      {{-- Langkah 2 --}}
      <div class="relative pl-6 sm:pl-8">
        <div class="absolute -left-3.5 top-1.5 w-7 h-7 rounded-full bg-teal-700 border-4 border-white shadow flex items-center justify-center text-white text-[10px] font-bold">
          2
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
          <h4 class="font-bold text-sm text-teal-950">Verifikasi & Konfirmasi Admin</h4>
          <p class="text-xs text-gray-500 leading-relaxed">
            Admin/Pengelola Studio-Lab akan memverifikasi kesesuaian berkas identitas dan ketersediaan barang/ruang pada tanggal yang diajukan. Status persetujuan akan diperbarui di dashboard peminjaman.
          </p>
        </div>
      </div>

      {{-- Langkah 3 --}}
      <div class="relative pl-6 sm:pl-8">
        <div class="absolute -left-3.5 top-1.5 w-7 h-7 rounded-full bg-teal-700 border-4 border-white shadow flex items-center justify-center text-white text-[10px] font-bold">
          3
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
          <h4 class="font-bold text-sm text-teal-950">Pengambilan & Penggunaan Inventaris</h4>
          <p class="text-xs text-gray-500 leading-relaxed">
            Jika disetujui, peminjam dapat mengambil peralatan di ruang pengelola studio/lab pada jam operasional kampus. Tunjukkan bukti KTM/KTP fisik saat proses pengambilan barang.
          </p>
        </div>
      </div>

      {{-- Langkah 4 --}}
      <div class="relative pl-6 sm:pl-8">
        <div class="absolute -left-3.5 top-1.5 w-7 h-7 rounded-full bg-teal-700 border-4 border-white shadow flex items-center justify-center text-white text-[10px] font-bold">
          4
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-1">
          <h4 class="font-bold text-sm text-teal-950">Pengembalian Tepat Waktu</h4>
          <p class="text-xs text-gray-500 leading-relaxed">
            Kembalikan barang sesuai tanggal pengembalian yang disetujui. Pengelola akan mengecek kelengkapan dan kondisi fisik barang. Kehilangan atau kerusakan barang menjadi tanggung jawab peminjam.
          </p>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection

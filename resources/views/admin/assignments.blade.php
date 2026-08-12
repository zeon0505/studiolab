@extends('layouts.admin')
@section('title', 'PJ Harian')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h2 class="text-[15px] font-bold text-slate-900">Penanggung Jawab Harian</h2>
        <p class="text-[12px] text-slate-400 mt-0.5">Atur staf UPT yang bertugas per hari. Pastikan nomor WA diisi agar notifikasi bot dapat terkirim.</p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] font-medium px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-[13px] font-medium px-4 py-3 rounded-xl space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                Terjadi Kesalahan:
            </div>
            <ul class="list-disc list-inside pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form Atur PJ & Tambah Staff Baru --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 text-[13px] mb-4">Atur Penugasan</h3>
                <form action="{{ route('admin.assignments.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Hari</label>
                        <select name="hari" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($days as $day)
                                <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Staff / PJ Bertugas</label>
                        <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}{{ $user->no_wa ? ' — '.$user->no_wa : ' (WA belum diisi)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-xl text-[13px] transition-colors">
                        Simpan Penugasan
                    </button>
                </form>
            </div>

            {{-- Form Tambah Staff Baru --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <h3 class="font-bold text-slate-800 text-[13px] mb-4">Daftarkan Staff / PJ Baru</h3>
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3" autocomplete="off">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama Staff" autocomplete="off"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[12px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Email</label>
                        <input type="email" name="email" required placeholder="email@staimas.com" autocomplete="off"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[12px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor WhatsApp (No WA)</label>
                        <input type="tel" name="no_wa" placeholder="081234567890" autocomplete="off"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[12px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Password Awal</label>
                        <input type="password" name="password" required placeholder="••••••" autocomplete="new-password"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[12px] focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                    <button type="submit" class="w-full bg-teal-800 hover:bg-teal-900 text-white font-bold py-2 rounded-xl text-[12px] transition-colors mt-2">
                        + Tambah Staff Baru
                    </button>
                </form>
            </div>

            {{-- Status API WA --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-lg {{ config('services.fonnte.token') ? 'bg-emerald-50' : 'bg-amber-50' }} flex items-center justify-center">
                        <i class="fab fa-whatsapp {{ config('services.fonnte.token') ? 'text-emerald-500' : 'text-amber-500' }} text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-slate-800">Bot WhatsApp</p>
                        <p class="text-[11px] {{ config('services.fonnte.token') ? 'text-emerald-600' : 'text-amber-500' }}">
                            {{ config('services.fonnte.token') ? '✓ Token dikonfigurasi' : '⚠ Token belum diset' }}
                        </p>
                    </div>
                </div>
                @if(!config('services.fonnte.token'))
                    <p class="text-[11px] text-slate-500 leading-relaxed">Daftar di <a href="https://fonnte.com" target="_blank" class="text-teal-600 font-medium hover:underline">fonnte.com</a> lalu isi <code class="bg-slate-100 px-1 py-0.5 rounded text-[10px]">FONNTE_TOKEN</code> di file <code class="bg-slate-100 px-1 py-0.5 rounded text-[10px]">.env</code></p>
                @endif
            </div>
        </div>

        {{-- Tabel PJ Harian --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900 text-[14px]">Jadwal PJ Mingguan</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($days as $day)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 text-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ strtoupper(substr($day, 0, 3)) }}</span>
                            </div>
                            <div>
                                @if(isset($assignments[$day]))
                                    <p class="text-[13px] font-semibold text-slate-900">{{ $assignments[$day]->user->name }}</p>
                                    <p class="text-[11px] text-slate-400">Penanggung Jawab UPT</p>
                                @else
                                    <p class="text-[13px] text-slate-400 italic">Belum ditentukan</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if(isset($assignments[$day]))
                                @if($assignments[$day]->user->no_wa)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $assignments[$day]->user->no_wa) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-emerald-600 hover:text-emerald-800 mr-2">
                                        <i class="fab fa-whatsapp"></i> {{ $assignments[$day]->user->no_wa }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-amber-500 font-medium mr-2">⚠ No WA belum diisi</span>
                                @endif
                                
                                <form id="delete-assignment-{{ $assignments[$day]->id }}" 
                                      action="{{ route('admin.assignments.destroy', $assignments[$day]->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="showConfirm('Hapus penugasan PJ untuk hari {{ ucfirst($day) }}? Slot hari itu akan kembali kosong.', () => document.getElementById('delete-assignment-{{ $assignments[$day]->id }}').submit(), { title: 'Hapus Penugasan PJ', okLabel: 'Ya, Hapus' })"
                                        class="text-red-500 hover:text-red-700 text-xs font-bold transition-colors">
                                        Hapus PJ
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-slate-300">—</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Update No WA Staff --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900 text-[14px]">Nomor WhatsApp Staff / PJ</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Nomor ini akan menerima notifikasi bot saat ada booking masuk.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($users as $user)
                <div class="flex items-center gap-3">
                    {{-- Avatar --}}
                    <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                    {{-- Nama + Update WA --}}
                    <form action="{{ route('admin.users.update-wa', $user->id) }}" method="POST" class="flex items-center gap-2 flex-1 min-w-0">
                        @csrf
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-semibold text-slate-700 truncate">{{ $user->name }}</p>
                            <input type="tel" name="no_wa" value="{{ $user->no_wa }}"
                                class="w-full px-2.5 py-1.5 mt-1 rounded-lg border border-slate-200 bg-slate-50 text-[12px] focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="0812-3456-7890">
                        </div>
                        <button type="submit"
                            class="w-8 h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center shrink-0 transition-colors">
                            <i class="fas fa-check text-xs"></i>
                        </button>
                    </form>

                    {{-- Hapus User --}}
                    <form id="del-staff-{{ $user->id }}"
                        action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                            onclick="if(confirm('Hapus akun {{ addslashes($user->name) }}? Tindakan ini tidak dapat diurungkan.')) document.getElementById('del-staff-{{ $user->id }}').submit()"
                            class="w-8 h-8 rounded-lg bg-red-100 hover:bg-red-500 text-red-500 hover:text-white flex items-center justify-center shrink-0 transition-colors"
                            title="Hapus akun">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

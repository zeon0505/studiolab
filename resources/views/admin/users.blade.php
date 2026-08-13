@extends('layouts.admin')
@section('title', 'Kelola Pengguna')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Kelola Pengguna</h1>
            <p class="text-[13px] text-slate-400 mt-0.5">Daftar semua pengguna yang terdaftar di portal</p>
        </div>
        <span class="inline-flex items-center gap-1.5 bg-teal-50 text-teal-700 text-[11px] font-bold px-3 py-1.5 rounded-xl border border-teal-100">
            <i class="fas fa-users text-xs"></i> {{ $users->total() }} Pengguna Terdaftar
        </span>
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

    {{-- Search and Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            {{-- Tabs Filter --}}
            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl w-fit">
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'all'])) }}"
                    class="px-4 py-1.5 rounded-lg text-[12px] font-bold transition-all duration-150
                    {{ $filterRole === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Semua
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'staff'])) }}"
                    class="px-4 py-1.5 rounded-lg text-[12px] font-bold transition-all duration-150
                    {{ $filterRole === 'staff' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Staff UPT
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->query(), ['role' => 'peminjam'])) }}"
                    class="px-4 py-1.5 rounded-lg text-[12px] font-bold transition-all duration-150
                    {{ $filterRole === 'peminjam' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    Pendaftar Peminjam
                </a>
            </div>

            {{-- Form Cari --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 flex-1 md:max-w-md">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau email..."
                    class="w-full px-4 py-2 rounded-xl border border-slate-200 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 bg-slate-50">
                <button type="submit"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-bold rounded-xl transition-colors whitespace-nowrap">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.users.index', ['role' => request('role')]) }}"
                        class="px-4 py-2 border border-slate-200 text-slate-600 text-[12px] font-bold rounded-xl hover:bg-slate-50 transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Peminjaman</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Terdaftar Sejak</th>
                        <th class="px-6 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider" colspan="2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- Identitas --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white text-[13px] font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- No WA --}}
                            <td class="px-6 py-4">
                                @if($user->no_wa)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $user->no_wa) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[12px] text-emerald-600 font-medium hover:underline">
                                        <i class="fab fa-whatsapp"></i> {{ $user->no_wa }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-300 italic">Belum diisi</span>
                                @endif
                            </td>

                            {{-- Jumlah Booking --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold
                                    {{ $user->bookings_count > 0 ? 'bg-teal-50 text-teal-700 border border-teal-100' : 'bg-slate-100 text-slate-400' }}">
                                    <i class="fas fa-box-archive text-[9px]"></i>
                                    {{ $user->bookings_count }} Peminjaman
                                </span>
                            </td>

                            {{-- Tanggal Daftar --}}
                            <td class="px-6 py-4">
                                <p class="text-[13px] text-slate-600">{{ $user->created_at->format('d M Y') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $user->created_at->diffForHumans() }}</p>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 min-w-[200px]">
                                <div class="flex flex-col gap-1.5">
                                    {{-- Ubah Password --}}
                                    <button
                                        data-id="{{ $user->id }}"
                                        data-nama="{{ $user->name }}"
                                        onclick="openResetModal(this)"
                                        class="w-full px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold rounded-lg transition-colors inline-flex items-center justify-center gap-1.5">
                                        <i class="fas fa-key text-xs"></i> Ubah Password
                                    </button>

                                    {{-- Hapus Akun --}}
                                    <form id="del-user-{{ $user->id }}"
                                        action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="if(confirm('Hapus akun {{ addslashes($user->name) }}? Semua data peminjaman terkait juga akan terpengaruh.')) document.getElementById('del-user-{{ $user->id }}').submit()"
                                            class="w-full px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[11px] font-bold rounded-lg transition-colors inline-flex items-center justify-center gap-1.5">
                                            <i class="fas fa-trash text-xs"></i> Hapus Akun
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-user-slash text-slate-300 text-xl"></i>
                                </div>
                                <p class="text-[13px] font-semibold text-slate-400">Belum ada pengguna terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal Reset Password --}}
<div id="modal-reset" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-900 text-[14px]">Ubah Password Pengguna</h3>
                <p id="reset-user-name" class="text-[12px] text-slate-400 mt-0.5"></p>
            </div>
            <button onclick="document.getElementById('modal-reset').classList.add('hidden')"
                class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form id="reset-form" action="" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Password Baru</label>
                <input type="password" name="password" required minlength="6"
                    placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="6"
                    placeholder="Ulangi password baru"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <div class="pt-2 flex gap-3">
                <button type="button" onclick="document.getElementById('modal-reset').classList.add('hidden')"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-[13px] font-bold rounded-xl hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-[13px] font-bold rounded-xl transition-colors">
                    <i class="fas fa-key mr-1"></i> Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(btn) {
    const id = btn.getAttribute('data-id');
    const nama = btn.getAttribute('data-nama');
    document.getElementById('reset-user-name').innerText = nama;
    document.getElementById('reset-form').action = `/admin/users/${id}/reset-password`;
    document.getElementById('modal-reset').classList.remove('hidden');
}
</script>
@endsection

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — UPT Studiolab STAIMAS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

    {{-- Left Panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-teal-800 to-teal-950 text-white p-12 flex-col justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-video text-white text-sm"></i>
            </div>
            <div>
                <p class="font-bold text-[13px] text-white">UPT Studiolab</p>
                <p class="text-[10px] text-teal-300">STAIMAS Wonogiri</p>
            </div>
        </div>
        <div class="space-y-4">
            <h2 class="text-3xl font-bold leading-tight">Sistem Peminjaman<br><span class="text-teal-300">Studio & Lab</span><br>Terintegrasi</h2>
            <p class="text-teal-200 text-[14px] leading-relaxed">Masuk ke akun Anda untuk mengajukan peminjaman peralatan dan ruangan secara online.</p>
            <div class="flex flex-col gap-3 pt-2">
                @foreach(['Booking peralatan & ruangan berbasis jam', 'Pantau status persetujuan real-time', 'Notifikasi konfirmasi dari PJ UPT'] as $feat)
                    <div class="flex items-center gap-2.5 text-[13px] text-teal-100">
                        <i class="fas fa-check-circle text-teal-400 text-xs"></i> {{ $feat }}
                    </div>
                @endforeach
            </div>
        </div>
        <p class="text-[11px] text-teal-600">© {{ date('Y') }} UPT Studio & Lab STAIMAS Wonogiri</p>
    </div>

    {{-- Right Panel --}}
    <div class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-sm space-y-7">
            <div>
                <h1 class="text-[22px] font-bold text-slate-900">Selamat datang kembali</h1>
                <p class="text-[13px] text-slate-500 mt-1">Masuk ke portal peminjaman UPT STAIMAS</p>
            </div>

            @if(session()->has('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-[12px] p-3.5 rounded-xl flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[12px] font-semibold text-slate-700 block">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                        placeholder="nama@email.com">
                    @error('email') <span class="text-red-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-[12px] font-semibold text-slate-700 block">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                        placeholder="Masukkan password">
                </div>

                <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-xl text-[13px] transition-all">
                    Masuk ke Akun
                </button>
            </form>



            <div class="text-center">
                <a href="{{ route('home') }}" class="text-[12px] text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i> Kembali ke beranda
                </a>
            </div>
        </div>
    </div>

</body>
</html>

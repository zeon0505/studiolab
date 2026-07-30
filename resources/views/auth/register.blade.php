<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — UPT Studiolab STAIMAS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">

        {{-- Header --}}
        <div class="text-center">
            <div class="w-11 h-11 bg-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-video text-white text-sm"></i>
            </div>
            <h1 class="text-[22px] font-bold text-slate-900">Buat Akun Baru</h1>
            <p class="text-[13px] text-slate-500 mt-1">Portal Peminjaman UPT STAIMAS Wonogiri</p>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-7 shadow-sm space-y-5">
            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[12px] font-semibold text-slate-700 block">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        placeholder="Contoh: Ahmad Fauzi">
                    @error('name') <span class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-[12px] font-semibold text-slate-700 block">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        placeholder="nama@email.com">
                    @error('email') <span class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-[12px] font-semibold text-slate-700 block">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="Min. 6 karakter">
                        @error('password') <span class="text-red-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[12px] font-semibold text-slate-700 block">Ulangi Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-[13px] focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                            placeholder="Ulangi">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-xl text-[13px] transition-all mt-2">
                    Daftar Akun
                </button>
            </form>

            <div class="text-center text-[12px] text-slate-500 pt-2 border-t border-slate-100">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-teal-600 hover:underline">Masuk di sini</a>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-[12px] text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-arrow-left mr-1 text-xs"></i> Kembali ke beranda
            </a>
        </div>
    </div>

</body>
</html>

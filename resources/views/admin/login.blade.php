<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - Studiolab Portal</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite Assets (Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 font-sans antialiased">

    <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-6 sm:p-8 w-full max-w-sm space-y-6">
        
        <div class="text-center space-y-2">
            <img src="https://staimaswonogiri.ac.id/wp-content/uploads/2020/07/LOGO-STAIMAS-AI.png" alt="STAIMAS Logo" class="w-12 h-12 object-contain mx-auto">
            <div>
                <h3 class="text-lg font-extrabold text-gray-900 tracking-tight">Portal Admin UPT</h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Studio & Lab STAIMAS Wonogiri</p>
            </div>
        </div>

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Email Administrator</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                    placeholder="nama@staimas.com">
                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-700"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-teal-700 hover:bg-teal-800 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-md shadow-teal-700/20">
                Masuk Dashboard
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-xs font-semibold text-teal-700 hover:underline">Kembali ke Beranda</a>
        </div>

    </div>

</body>
</html>

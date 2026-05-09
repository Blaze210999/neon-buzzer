<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pilih Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen p-6 flex items-center justify-center">

    <div class="max-w-2xl w-full bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">
        <h1
            class="text-3xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mb-2 uppercase tracking-widest">
            KONTROL PUSAT
        </h1>
        <p class="text-center text-gray-500 mb-8 uppercase text-sm tracking-widest border-b border-gray-800 pb-6">Pilih
            Menu</p>

        @if (session('success'))
            <div
                class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-xl mb-6 text-center font-bold">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6">
            <!-- Kartu Mode 1 -->
            <a href="{{ route('game.admin.control') }}"
                class="group block bg-gray-800 border border-gray-700 hover:border-pink-500 hover:bg-gray-800/80 rounded-2xl p-6 transition-all duration-300 shadow-lg hover:shadow-[0_0_30px_rgba(236,72,153,0.3)]">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white group-hover:text-pink-400 transition-colors mb-2">MODE
                            1: Rebutan Cepat</h2>
                        <p class="text-gray-400 text-sm">Masuk ke panel kendali utama untuk kuis.</p>
                    </div>
                    <div
                        class="bg-pink-500/20 text-pink-500 p-4 rounded-full group-hover:bg-pink-500 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Kartu Pengaturan -->
            <a href="{{ route('game.admin.settings') }}"
                class="group block bg-gray-800 border border-gray-700 hover:border-cyan-500 rounded-2xl p-6 transition-all shadow-lg mt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-300 group-hover:text-cyan-400 transition-colors mb-1">⚙️
                            PENGATURAN KUIS</h2>
                        <p class="text-gray-500 text-sm">Ubah durasi timer rebutan, timer menjawab, dan jumlah poin.</p>
                    </div>
                </div>
            </a>
            <!-- Tombol Audit Log -->
            <a href="{{ route('game.admin.logs') }}"
                class="group block bg-gray-800 border border-gray-700 hover:border-purple-500 rounded-2xl p-6 transition-all shadow-lg mt-2 relative overflow-hidden">
                <div class="absolute inset-0 bg-purple-500/5 group-hover:bg-purple-500/10 transition"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <h2 class="text-xl font-bold text-gray-300 group-hover:text-purple-400 transition-colors mb-1">
                            🕵️‍♂️ AUDIT LOG (VAR)</h2>
                        <p class="text-gray-500 text-sm">Lihat riwayat lengkap siapa yang memencet bel, penambahan poin,
                            dan penalti.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</body>

</html>

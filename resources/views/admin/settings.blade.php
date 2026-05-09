<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Kuis</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen p-6 flex flex-col items-center justify-center">

    <div class="max-w-xl w-full bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">
        <div class="flex items-center gap-4 mb-8 border-b border-gray-800 pb-6">
            <a href="{{ route('game.admin.dashboard') }}" class="text-gray-500 hover:text-white transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1
                class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 uppercase tracking-widest">
                PENGATURAN KUIS
            </h1>
        </div>

        <form action="{{ route('game.admin.settings.save') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-6">
                <!-- Timers -->
                <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700">
                    <label class="block text-gray-400 text-sm font-bold mb-2 uppercase tracking-widest">Rebutan
                        (Detik)</label>
                    <input type="number" name="timer_rebutan" value="{{ $room->timer_rebutan }}"
                        class="w-full bg-gray-900 text-cyan-400 font-mono text-2xl font-bold rounded-xl border border-gray-600 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 px-4 py-3 outline-none transition">
                </div>

                <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700">
                    <label class="block text-gray-400 text-sm font-bold mb-2 uppercase tracking-widest">Jawab
                        (Detik)</label>
                    <input type="number" name="timer_menjawab" value="{{ $room->timer_menjawab }}"
                        class="w-full bg-gray-900 text-yellow-400 font-mono text-2xl font-bold rounded-xl border border-gray-600 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 px-4 py-3 outline-none transition">
                </div>

                <!-- Points -->
                <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700">
                    <label class="block text-gray-400 text-sm font-bold mb-2 uppercase tracking-widest">Poin Benar
                        (+)</label>
                    <input type="number" name="poin_benar" value="{{ $room->poin_benar }}"
                        class="w-full bg-gray-900 text-green-400 font-mono text-2xl font-bold rounded-xl border border-gray-600 focus:border-green-500 focus:ring-1 focus:ring-green-500 px-4 py-3 outline-none transition">
                </div>

                <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700">
                    <label class="block text-gray-400 text-sm font-bold mb-2 uppercase tracking-widest">Poin Salah
                        (-)</label>
                    <input type="number" name="poin_salah" value="{{ $room->poin_salah }}"
                        class="w-full bg-gray-900 text-red-400 font-mono text-2xl font-bold rounded-xl border border-gray-600 focus:border-red-500 focus:ring-1 focus:ring-red-500 px-4 py-3 outline-none transition">
                </div>
            </div>

            <button type="submit"
                class="w-full mt-8 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-black text-xl py-5 rounded-2xl shadow-[0_0_20px_rgba(34,211,238,0.3)] transition transform hover:scale-105 uppercase tracking-widest">
                💾 SIMPAN PENGATURAN
            </button>
        </form>
    </div>
</body>

</html>

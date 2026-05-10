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
            <a href="{{ route('game.admin.dashboard') }}"
                class="text-gray-500 hover:text-white transition bg-gray-800 p-2 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1
                class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 uppercase tracking-widest">
                ⚙️ PENGATURAN KUIS
            </h1>
        </div>

        <form action="{{ route('game.admin.settings.save') }}" method="POST" class="space-y-8">
            @csrf

            <div>
                <h2 class="text-pink-500 font-bold uppercase tracking-widest mb-4 border-b border-gray-800 pb-2">🎯 Mode
                    1 (Rebutan & Poin)</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Waktu Rebutan
                            (Detik)</label>
                        <input type="number" name="timer_rebutan" value="{{ $room->timer_rebutan }}"
                            class="w-full bg-gray-900 text-cyan-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 px-4 py-2 outline-none transition">
                    </div>
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Waktu Jawab (Detik)</label>
                        <input type="number" name="timer_menjawab" value="{{ $room->timer_menjawab }}"
                            class="w-full bg-gray-900 text-yellow-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 px-4 py-2 outline-none transition">
                    </div>
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Poin Benar (+)</label>
                        <input type="number" name="poin_benar" value="{{ $room->poin_benar }}"
                            class="w-full bg-gray-900 text-green-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-green-500 focus:ring-1 focus:ring-green-500 px-4 py-2 outline-none transition">
                    </div>
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Poin Salah (-)</label>
                        <input type="number" name="poin_salah" value="{{ $room->poin_salah }}"
                            class="w-full bg-gray-900 text-red-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-red-500 focus:ring-1 focus:ring-red-500 px-4 py-2 outline-none transition">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-purple-500 font-bold uppercase tracking-widest mb-4 border-b border-gray-800 pb-2">🎵
                    Mode 2 (Tebak Lagu)</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Waktu Rebutan
                            (Detik)</label>
                        <input type="number" name="m2_timer_rebutan" value="{{ $room->m2_timer_rebutan }}"
                            class="w-full bg-gray-900 text-cyan-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 px-4 py-2 outline-none transition">
                    </div>
                    <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-inner">
                        <label class="block text-gray-400 text-xs font-bold mb-2 uppercase">Waktu Jawab (Detik)</label>
                        <input type="number" name="m2_timer_menjawab" value="{{ $room->m2_timer_menjawab }}"
                            class="w-full bg-gray-900 text-yellow-400 font-mono text-xl font-bold rounded-xl border border-gray-600 focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 px-4 py-2 outline-none transition">
                    </div>
                </div>
                <div class="bg-gray-800 p-5 rounded-2xl border border-gray-700 shadow-inner">
                    <label class="block text-gray-400 text-xs font-bold mb-3 uppercase tracking-widest">Kapan Timer
                        Rebutan Dimulai?</label>
                    <select name="m2_timer_start"
                        class="w-full bg-gray-900 text-white font-bold rounded-xl border border-gray-600 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 px-4 py-3 outline-none cursor-pointer">
                        <option value="after" {{ $room->m2_timer_start == 'after' ? 'selected' : '' }}>⏳ SETELAH Lagu
                            Selesai Diputar</option>
                        <option value="during" {{ $room->m2_timer_start == 'during' ? 'selected' : '' }}>⚡ BERSAMAAN
                            Saat Lagu Diputar</option>
                    </select>
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

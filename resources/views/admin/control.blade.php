<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kendali Kuis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen p-6 relative" x-data="adminPanel({{ $room->id }})">

    <div class="max-w-xl mx-auto bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl">
        <h1
            class="text-2xl font-bold text-center text-gray-400 mb-8 uppercase tracking-widest border-b border-gray-800 pb-4">
            Remote Kendali Admin</h1>

        <div x-show="!answeringPlayer" class="flex flex-col gap-4">
            <button @click="control('start')"
                class="w-full bg-green-600 hover:bg-green-500 text-white text-2xl font-black py-6 rounded-2xl shadow-[0_0_20px_rgba(34,197,94,0.3)] transition transform hover:scale-105">
                ▶️ MULAI REBUTAN (10s)
            </button>
            <button @click="control('reset')"
                class="w-full bg-gray-700 hover:bg-red-600 text-white text-lg font-bold py-4 rounded-2xl transition">
                ⏹️ BATALKAN / RESET
            </button>
        </div>

        <div x-show="answeringPlayer" style="display: none;"
            class="text-center bg-gray-800 border border-pink-500/50 p-6 rounded-2xl shadow-[0_0_30px_rgba(236,72,153,0.3)]">
            <p class="text-gray-400 mb-2 text-sm uppercase tracking-widest">Pilih Multiplier Skor:</p>

            <div class="flex justify-center gap-2 mb-6">
                <template x-for="m in [1, 2, 3]">
                    <button @click="multiplier = m"
                        :class="multiplier === m ? 'bg-pink-500 text-white shadow-[0_0_15px_rgba(236,72,153,0.5)]' :
                            'bg-gray-700 text-gray-400'"
                        class="px-6 py-2 rounded-xl font-bold transition-all" x-text="m + 'x'">
                    </button>
                </template>
            </div>

            <h2 class="text-3xl font-black text-pink-500 uppercase mb-6" x-text="answeringPlayer?.name"></h2>

            <div class="flex flex-col gap-4">
                <button @click="gradeAnswer(true)"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xl font-black py-4 rounded-2xl">
                    ✅ BENAR (+<span x-text="100 * multiplier"></span>)
                </button>
                <button @click="gradeAnswer(false)"
                    class="w-full bg-red-600 hover:bg-red-500 text-white text-xl font-black py-4 rounded-2xl">
                    ❌ SALAH (-<span x-text="50 * multiplier"></span>)
                </button>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-800">
            <h3 class="text-sm text-gray-500 mb-4 uppercase">Status Pemain ({{ $players->count() }})</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($players as $p)
                    <span
                        class="bg-gray-800 text-sm px-4 py-2 rounded-xl text-gray-300 flex items-center gap-2 border border-gray-700">
                        {{ $p->name }}
                        <span
                            class="bg-gray-900 text-yellow-400 font-bold px-2 py-0.5 rounded-md">{{ $p->score }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="mt-8 pt-4">
            <button @click="showResetModal = true"
                class="w-full py-4 border border-red-500/30 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-xl text-sm font-bold transition-all uppercase tracking-widest">
                Reset Semua Skor Pemain
            </button>
        </div>
    </div>

    <div x-show="showResetModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 transition-opacity">
        <div @click.away="showResetModal = false"
            class="bg-gray-900 border border-red-500 p-8 rounded-3xl shadow-[0_0_50px_rgba(220,38,38,0.4)] max-w-sm w-full text-center transform scale-100 transition-transform">

            <div class="text-red-500 mb-6 flex justify-center">
                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
            </div>

            <h3 class="text-2xl font-black text-white mb-2 uppercase">Yakin Reset Skor?</h3>
            <p class="text-gray-400 mb-8 text-sm">Semua tim akan kembali memiliki 0 poin. Tindakan ini tidak bisa
                dibatalkan.</p>

            <div class="flex gap-4">
                <button @click="showResetModal = false"
                    class="flex-1 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white font-bold py-3 rounded-xl transition">Batal</button>
                <button @click="executeResetScores"
                    class="flex-1 bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-xl shadow-[0_0_15px_rgba(220,38,38,0.5)] transition">Ya,
                    Reset!</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminPanel', (roomId) => ({
                answeringPlayer: null,
                multiplier: 1,
                showResetModal: false, // Kontrol pop-up modal

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'reset') {
                                this.answeringPlayer = null;
                                this.multiplier = 1;
                            }
                        })
                        .listen('PlayerBuzzed', (e) => {
                            this.answeringPlayer = e.player;
                        });
                },

                control(action) {
                    axios.post(`/api/room/neon/control`, {
                        action: action,
                        duration: 10
                    });
                },

                gradeAnswer(isCorrect) {
                    if (!this.answeringPlayer) return;
                    axios.post(`/api/room/neon/grade`, {
                        player_id: this.answeringPlayer.id,
                        is_correct: isCorrect,
                        multiplier: this.multiplier
                    }).then(() => {
                        location.reload(); // Refresh panel admin agar skor terbaru muncul
                    });
                },

                // Fungsi Reset Skor yang diperbaiki
                executeResetScores() {
                    axios.post(`/api/room/neon/reset-scores`).then(() => {
                        this.showResetModal = false; // Tutup modal
                        location.reload(); // Refresh panel admin agar skor menjadi 0
                    }).catch(err => {
                        alert("Gagal mereset skor. Pastikan koneksi server aman.");
                    });
                }
            }));
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Mode 1 (Rebutan)</title>
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

<body class="bg-gray-950 text-white min-h-screen p-6 flex items-center justify-center" x-data="mode1Panel({{ $room->id }})">

    <div class="max-w-xl w-full bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">

        <div class="flex justify-between items-center border-b border-gray-800 pb-6 mb-8">
            <a href="{{ route('game.admin.dashboard') }}"
                class="text-gray-500 hover:text-white transition bg-gray-800 p-2 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-black text-pink-500 uppercase tracking-widest">🎯 MODE 1: REBUTAN</h1>
            <div class="w-10"></div>
        </div>

        <div x-show="!answeringPlayer" class="flex flex-col gap-6">
            <button @click="control('start')"
                class="w-full bg-green-600 hover:bg-green-500 text-white text-3xl font-black py-10 rounded-[2rem] shadow-[0_0_30px_rgba(34,197,94,0.4)] transition transform hover:scale-105">
                ▶️ MULAI REBUTAN<br><span
                    class="text-sm font-normal text-green-200 block mt-2">({{ $room->timer_rebutan }} Detik)</span>
            </button>
            <button @click="control('reset')"
                class="w-full bg-gray-800 border border-gray-700 hover:bg-gray-700 text-gray-400 hover:text-white text-xl font-bold py-6 rounded-2xl transition">
                ⏹️ BATALKAN / RESET BEL
            </button>
        </div>

        <div x-show="answeringPlayer" style="display: none;"
            class="text-center bg-gray-800 border-2 border-pink-500 p-8 rounded-[2rem] shadow-[0_0_40px_rgba(236,72,153,0.3)] animate-pulse">
            <p class="text-gray-400 mb-4 text-sm uppercase tracking-widest font-bold">Multiplier Skor:</p>
            <div class="flex justify-center gap-3 mb-8">
                <template x-for="m in [1, 2, 3]">
                    <button @click="multiplier = m"
                        :class="multiplier === m ? 'bg-pink-500 text-white shadow-[0_0_15px_rgba(236,72,153,0.5)]' :
                            'bg-gray-700 text-gray-400'"
                        class="px-6 py-3 rounded-xl font-black text-xl transition-all" x-text="m + 'x'"></button>
                </template>
            </div>
            <h2 class="text-5xl font-black text-pink-500 uppercase mb-8 drop-shadow-md" x-text="answeringPlayer?.name">
            </h2>

            <div class="flex flex-col gap-4">
                <button @click="gradeAnswer(true)"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white text-2xl font-black py-6 rounded-2xl shadow-lg transition transform hover:scale-105">
                    ✅ BENAR (+<span x-text="{{ $room->poin_benar }} * multiplier"></span>)
                </button>
                <button @click="gradeAnswer(false)"
                    class="w-full bg-red-600 hover:bg-red-500 text-white text-2xl font-black py-6 rounded-2xl shadow-lg transition transform hover:scale-105">
                    ❌ SALAH (-<span x-text="{{ $room->poin_salah }} * multiplier"></span>)
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mode1Panel', (roomId) => ({
                answeringPlayer: null,
                multiplier: 1,
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
                // KIRIM PARAMETER MODE 1
                control(action) {
                    axios.post(`/api/room/neon/control`, {
                        action: action,
                        duration: {{ $room->timer_rebutan }},
                        mode: 'mode1'
                    });
                },
                gradeAnswer(isCorrect) {
                    if (!this.answeringPlayer) return;
                    axios.post(`/api/room/neon/grade`, {
                            player_id: this.answeringPlayer.id,
                            is_correct: isCorrect,
                            multiplier: this.multiplier
                        })
                        .then(() => location.reload());
                }
            }));
        });
    </script>
</body>

</html>

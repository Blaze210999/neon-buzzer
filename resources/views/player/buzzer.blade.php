<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buzzer - {{ $player->name }}</title>

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
    <style>
        button {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>

<body class="bg-gray-900 text-white h-[100dvh] overflow-hidden flex flex-col" x-data="playerBuzzer({{ $room->id }})">

    <div class="p-5 text-center bg-gray-800 border-b border-gray-700 shadow-lg">
        <h2 class="text-xl text-gray-400">Tim: <span class="font-bold text-white uppercase">{{ $player->name }}</span>
        </h2>
        <h3 class="text-sm mt-1 text-cyan-400 font-mono">Skor: {{ $player->score }}</h3>

        <div class="mt-3 inline-block px-6 py-2 rounded-full text-sm font-black tracking-widest uppercase transition-colors"
            :class="{
                'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50': status === 'waiting',
                'bg-green-500/20 text-green-400 border border-green-500/50': status === 'playing',
                'bg-pink-500/20 text-pink-400 border border-pink-500/50': status === 'answering',
                'bg-red-500/20 text-red-400 border border-red-500/50': status === 'locked'
            }"
            x-text="statusText">
        </div>
    </div>

    <div class="flex-grow flex items-center justify-center p-6 relative">
        <div x-show="status === 'answering'" x-transition.opacity class="absolute inset-0 bg-pink-500/20 animate-pulse">
        </div>

        <button @click="buzz" :disabled="status !== 'playing' || isBuzzing"
            class="w-full aspect-square max-w-[350px] rounded-full text-5xl font-black transition-all duration-100 transform active:scale-90 shadow-[0_20px_50px_rgba(0,0,0,0.5)] relative z-10 flex flex-col items-center justify-center"
            :class="{
                'bg-gray-800 text-gray-600 border-8 border-gray-700': status === 'waiting' || status === 'locked',
                'bg-gradient-to-b from-green-400 to-green-600 text-white shadow-[0_0_80px_rgba(34,197,94,0.8)] border-8 border-green-300': status === 'playing',
                'bg-gradient-to-b from-pink-400 to-pink-600 text-white shadow-[0_0_100px_rgba(236,72,153,1)] border-8 border-pink-300': status === 'answering'
            }">
            <span x-text="buttonText" class="drop-shadow-md text-center px-4"></span>
        </button>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('playerBuzzer', (roomId) => ({
                status: 'waiting',
                statusText: 'MENUNGGU HOST',
                buttonText: 'TUNGGU',
                isBuzzing: false,

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'start') {
                                this.status = 'playing';
                                this.statusText = 'SIAP REBUTAN!';
                                this.buttonText = 'BUZZ!';
                                if (navigator.vibrate) navigator.vibrate(100);
                            } else if (e.action === 'reset') {
                                // Refresh halaman agar skor terbaru muncul
                                location.reload();
                            }
                        })
                        .listen('PlayerBuzzed', (e) => {
                            if (e.player.name === '{{ $player->name }}') {
                                this.status = 'answering';
                                this.statusText = 'WAKTU MENJAWAB!';
                                this.buttonText = 'JAWAB!';
                                if (navigator.vibrate) navigator.vibrate([200, 100, 500]);
                            } else {
                                this.status = 'locked';
                                this.statusText = 'KEDULUAN TEMAN';
                                this.buttonText = 'TELAT';
                            }
                        });
                },

                async buzz() {
                    if (this.status !== 'playing' || this.isBuzzing) return;
                    this.isBuzzing = true;

                    try {
                        // UBAH BARIS INI: Sisipkan player_id saat mengirim data
                        let res = await axios.post(`/api/room/neon/buzz`, {
                            player_id: {{ $player->id }}
                        });

                        if (res.data.status === 'late') {
                            this.status = 'locked';
                            this.statusText = 'KEDULUAN TEMAN';
                            this.buttonText = 'TELAT';
                        }
                    } catch (error) {
                        console.error("Gagal mengirim buzz", error);
                    } finally {
                        this.isBuzzing = false;
                    }
                }
            }));
        });
    </script>
</body>

</html>

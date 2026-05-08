<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Kuis - Neon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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

<body class="bg-gray-900 text-white h-screen font-sans overflow-hidden flex flex-col" x-data="displayScreen({{ $room->id }})">

    <div class="container mx-auto p-8 h-full flex flex-col">
        <div class="flex justify-between items-center border-b border-gray-700 pb-6 mb-8">
            <h1
                class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 tracking-widest uppercase">
                NEON QUIZ
            </h1>
            <div class="flex items-center gap-6 bg-gray-800 p-4 rounded-2xl border border-gray-700">
                <div class="bg-white p-2 rounded-xl">{!! $qrCode !!}</div>
                <div>
                    <p class="text-gray-400 text-sm tracking-widest mb-1">SCAN UNTUK JOIN</p>
                    <p class="text-2xl text-cyan-400 font-mono font-bold">{{ $joinUrl }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 flex-grow">
            <div class="lg:col-span-2 flex flex-col items-center justify-center bg-gray-800 p-8 rounded-[3rem] border border-gray-700 shadow-[0_0_50px_rgba(0,0,0,0.5)] relative overflow-hidden transition-all duration-300"
                :class="{ 'border-pink-500 shadow-[0_0_80px_rgba(236,72,153,0.6)]': answeringPlayer }">

                <div x-show="answeringPlayer" class="absolute inset-0 bg-pink-500/10 animate-pulse"></div>

                <div x-show="!answeringPlayer"
                    class="text-[15rem] font-black font-mono leading-none tracking-tighter transition-colors"
                    :class="timeLeft <= 3 && timeLeft > 0 ? 'text-red-500 drop-shadow-[0_0_40px_rgba(239,68,68,0.8)]' :
                        'text-cyan-400 drop-shadow-[0_0_40px_rgba(34,211,238,0.8)]'"
                    x-text="displayTime">
                </div>
                <p x-show="!answeringPlayer" class="text-3xl text-gray-500 mt-4 tracking-widest font-light"
                    x-text="timeLeft > 0 ? 'WAKTU REBUTAN' : 'MENUNGGU SOAL'"></p>

                <div x-show="answeringPlayer" class="text-center relative z-10 w-full" style="display: none;">
                    <p class="text-4xl text-gray-300 mb-4 tracking-widest">KESEMPATAN MENJAWAB:</p>
                    <h2 class="text-[7rem] font-black text-pink-500 drop-shadow-[0_0_50px_rgba(236,72,153,1)] uppercase leading-none mb-6"
                        x-text="answeringPlayer?.name"></h2>
                    <div class="text-[6rem] font-mono font-bold text-yellow-400 drop-shadow-lg" x-text="answerTimeLeft">
                    </div>
                    <p class="text-2xl text-yellow-500/50 mt-2 tracking-widest">DETIK TERSISA</p>
                </div>
            </div>

            <div class="bg-gray-800 p-8 rounded-[3rem] border border-gray-700 flex flex-col shadow-xl">
                <h2
                    class="text-3xl mb-8 text-cyan-400 font-black uppercase tracking-widest border-b border-gray-700 pb-6 text-center">
                    KLASEMEN SKOR</h2>
                <ul class="space-y-5 overflow-y-auto flex-grow pr-2">
                    @foreach ($players as $index => $p)
                        <li
                            class="flex justify-between items-center bg-gray-700/40 p-5 rounded-2xl border border-gray-600">
                            <div class="flex items-center gap-4">
                                <span class="text-2xl font-bold text-gray-500">#{{ $index + 1 }}</span>
                                <span class="font-bold text-3xl text-white">{{ $p->name }}</span>
                            </div>
                            <span
                                class="text-yellow-400 font-black text-3xl bg-gray-900 px-5 py-2 rounded-xl border border-yellow-500/30">{{ $p->score }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('displayScreen', (roomId) => ({
                timeLeft: 0,
                displayTime: '00.0',
                timerInterval: null,
                answeringPlayer: null,
                answerTimeLeft: 30,
                answerTimerInterval: null,
                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'start') this.startTimer(new Date(e.timerEndsAt)
                                .getTime());
                            else if (e.action === 'reset') location
                                .reload(); // Refresh langsung untuk update skor klasemen
                        })
                        .listen('PlayerBuzzed', (e) => {
                            clearInterval(this.timerInterval);
                            this.answeringPlayer = e.player;
                            this.startAnswerTimer();
                        });
                },
                startTimer(endsAt) {
                    this.resetGame();
                    this.timerInterval = setInterval(() => {
                        let diff = endsAt - new Date().getTime();
                        if (diff <= 0) {
                            clearInterval(this.timerInterval);
                            this.displayTime = '00.0';
                            this.timeLeft = 0;
                        } else {
                            this.timeLeft = diff / 1000;
                            this.displayTime = this.timeLeft.toFixed(1);
                        }
                    }, 100);
                },
                startAnswerTimer() {
                    this.answerTimeLeft = 30; // Mulai dari 30 detik
                    clearInterval(this.answerTimerInterval);

                    this.answerTimerInterval = setInterval(() => {
                        if (this.answerTimeLeft > 0) {
                            this.answerTimeLeft--;
                        } else {
                            // SAAT WAKTU HABIS (0)
                            clearInterval(this.answerTimerInterval);

                            // Panggil API Timeout
                            axios.post(`/api/room/neon/timeout`, {
                                player_id: this.answeringPlayer.id
                            }).catch(err => {
                                console.error(err);
                                alert(
                                    "Gagal memproses timeout otomatis! Cek terminal server.");
                                // Jika gagal terhubung ke server, paksa layar untuk reload
                                location.reload();
                            });
                        }
                    }, 1000);
                },
                resetGame() {
                    clearInterval(this.timerInterval);
                    clearInterval(this.answerTimerInterval);
                    this.displayTime = '10.0';
                    this.answeringPlayer = null;
                    this.timeLeft = 10;
                }
            }));
        });
    </script>
</body>

</html>

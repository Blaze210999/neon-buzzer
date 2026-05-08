<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard - Neon Quiz</title>

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

<body class="bg-gray-900 text-white min-h-screen font-sans selection:bg-pink-500 flex flex-col" x-data="hostGame({{ $room->id }})">

    <div class="container mx-auto p-4 flex-grow flex flex-col">
        <div class="flex justify-between items-center border-b border-gray-700 pb-4 mb-6">
            <h1
                class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-violet-500 tracking-wider">
                NEON QUIZ
            </h1>
            <div class="flex gap-4">
                <button @click="control('start')"
                    class="bg-green-500 hover:bg-green-400 text-gray-900 px-8 py-3 rounded-full font-bold shadow-[0_0_15px_rgba(34,197,94,0.6)] uppercase tracking-wide">Mulai
                    (10 Detik)</button>
                <button @click="control('reset')"
                    class="bg-red-500 hover:bg-red-400 text-white px-8 py-3 rounded-full font-bold shadow-[0_0_15px_rgba(239,68,68,0.6)] uppercase tracking-wide">Batal/Reset</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 flex-grow">
            <div class="bg-gray-800 p-6 rounded-3xl border border-gray-700 flex flex-col items-center justify-center">
                <h2 class="text-xl mb-6 text-gray-400 font-semibold uppercase tracking-widest">Scan untuk Join</h2>
                <div class="bg-white p-4 rounded-2xl inline-block mb-6">
                    {!! $qrCode !!}
                </div>
                <p class="text-lg text-cyan-400 font-mono bg-gray-900 px-4 py-2 rounded-lg">{{ $joinUrl }}</p>
            </div>

            <div class="flex flex-col items-center justify-center bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-[0_0_40px_rgba(0,0,0,0.5)] relative overflow-hidden"
                :class="{ 'border-pink-500 shadow-[0_0_60px_rgba(236,72,153,0.6)]': answeringPlayer }">

                <div x-show="answeringPlayer" class="absolute inset-0 bg-pink-500/10 animate-pulse"></div>

                <div x-show="!answeringPlayer" class="text-[10rem] font-black font-mono leading-none tracking-tighter"
                    :class="timeLeft <= 3 && timeLeft > 0 ? 'text-red-500' :
                        'text-cyan-400 drop-shadow-[0_0_20px_rgba(34,211,238,0.8)]'"
                    x-text="displayTime">
                </div>

                <div x-show="answeringPlayer" class="text-center relative z-10 w-full" style="display: none;">
                    <p class="text-2xl text-gray-300 mb-2 tracking-widest">YANG MENJAWAB:</p>
                    <h2 class="text-6xl font-black text-pink-500 drop-shadow-[0_0_30px_rgba(236,72,153,1)] uppercase mb-4"
                        x-text="answeringPlayer?.name"></h2>

                    <div class="text-5xl font-mono text-yellow-400 mb-8" x-text="answerTimeLeft + ' Detik'"></div>

                    <div class="flex gap-4 justify-center mt-4">
                        <button @click="gradeAnswer(true)"
                            class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-4 rounded-xl font-black text-2xl shadow-[0_0_20px_rgba(37,99,235,0.6)] transition transform hover:scale-105">
                            ✅ BENAR (+100)
                        </button>
                        <button @click="gradeAnswer(false)"
                            class="bg-red-600 hover:bg-red-500 text-white px-8 py-4 rounded-xl font-black text-2xl shadow-[0_0_20px_rgba(220,38,38,0.6)] transition transform hover:scale-105">
                            ❌ SALAH (-50)
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 p-6 rounded-3xl border border-gray-700 flex flex-col">
                <h2
                    class="text-xl mb-6 text-gray-400 font-semibold uppercase tracking-widest border-b border-gray-700 pb-4">
                    Klasemen</h2>
                <ul class="space-y-4 overflow-y-auto flex-grow">
                    @foreach ($players as $p)
                        <li
                            class="flex justify-between items-center bg-gray-700/50 p-4 rounded-xl border border-gray-600">
                            <span class="font-bold text-xl text-white">{{ $p->name }}</span>
                            <span
                                class="text-yellow-400 font-black text-2xl bg-gray-900 px-4 py-1 rounded-lg">{{ $p->score }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('hostGame', (roomId) => ({
                timeLeft: 0,
                displayTime: '00.0',
                timerInterval: null,
                answeringPlayer: null,
                answerTimeLeft: 30, // UBAH KE 30 DETIK
                answerTimerInterval: null,

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'start') this.startTimer(new Date(e.timerEndsAt)
                                .getTime());
                            else if (e.action === 'reset') this.resetGame();
                        })
                        .listen('PlayerBuzzed', (e) => {
                            clearInterval(this.timerInterval); // Stop timer rebutan
                            this.answeringPlayer = e.player;
                            this.startAnswerTimer(); // Mulai timer menjawab
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
                    this.answerTimeLeft = 30; // MULAI DARI 30
                    clearInterval(this.answerTimerInterval);
                    this.answerTimerInterval = setInterval(() => {
                        if (this.answerTimeLeft > 0) {
                            this.answerTimeLeft--;
                        } else {
                            clearInterval(this.answerTimerInterval);
                        }
                    }, 1000);
                },

                resetGame() {
                    clearInterval(this.timerInterval);
                    clearInterval(this.answerTimerInterval);
                    this.displayTime = '10.0';
                    this.answeringPlayer = null;
                    this.timeLeft = 10;
                },

                // Sisanya tetap sama (control dan gradeAnswer)
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
                        is_correct: isCorrect
                    }).then(() => location.reload());
                }
            }));
        });
    </script>
</body>

</html>

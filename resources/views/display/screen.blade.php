<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Kuis - Neon</title>

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
        [x-cloak] {
            display: none !important;
        }

        .leaderboard-item {
            height: 6rem;
            /* 96px */
            transition: transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1),
                background-color 0.5s ease, border-color 0.5s ease, box-shadow 0.5s ease;
            will-change: transform, background-color;
        }
    </style>
</head>

<body class="bg-gray-900 text-white h-screen font-sans overflow-hidden flex flex-col relative" x-data="displayScreen({{ $room->id }}, {{ $players->toJson() }}, {{ cache('room_locked_' . $room->id) ? 'false' : 'true' }}, {{ $room->timer_menjawab }})">

    <!-- TOMBOL AKTIVASI AUDIO -->
    <div x-show="!audioEnabled" x-cloak
        class="absolute inset-0 z-[100] bg-gray-900/90 backdrop-blur-sm flex flex-col items-center justify-center">
        <h2 class="text-3xl font-bold mb-6">Layar Proyektor Siap</h2>
        <button @click="enableAudio()"
            class="bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-black text-2xl px-10 py-5 rounded-full shadow-[0_0_30px_rgba(34,211,238,0.5)] animate-bounce">
            🔊 KLIK UNTUK MENGAKTIFKAN SUARA
        </button>
    </div>

    <div class="container mx-auto p-8 h-full flex flex-col">

        <!-- HEADER -->
        <div class="flex justify-between items-center border-b border-gray-700 pb-6 mb-8">
            <h1
                class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 uppercase tracking-tighter drop-shadow-sm">
                QUIZ</h1>

            <div x-show="!isLobby" x-cloak
                class="flex items-center gap-6 bg-gray-800 p-4 rounded-2xl border border-gray-700 shadow-lg">
                <div class="bg-white p-2 rounded-xl">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->generate($joinUrl) !!}</div>
                <div>
                    <p class="text-gray-400 text-xs tracking-widest mb-1">JOIN SEKARANG</p>
                    <p class="text-xl text-cyan-400 font-mono font-bold">{{ $joinUrl }}</p>
                </div>
            </div>
        </div>

        <!-- LOBBY SCREEN -->
        <div x-show="isLobby" x-cloak
            class="absolute inset-0 z-50 bg-gray-900 flex flex-col items-center justify-center p-10"
            :class="{ 'hidden': !audioEnabled }">
            <div
                class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-cyan-900/20 via-gray-900 to-gray-900 animate-pulse">
            </div>

            <h1
                class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 tracking-widest uppercase mb-12 relative z-10 drop-shadow-lg">
                NEON QUIZ LOBBY
            </h1>

            <div class="flex items-center gap-16 relative z-10">
                <div class="flex flex-col items-center">
                    <div
                        class="bg-white p-6 rounded-3xl shadow-[0_0_50px_rgba(34,211,238,0.5)] mb-6 border-4 border-cyan-400">
                        {!! $qrCode !!}</div>
                    <p class="text-gray-400 tracking-widest uppercase text-sm mb-2">Scan atau ketik link di bawah:</p>
                    <p
                        class="text-3xl text-cyan-400 font-mono font-bold bg-gray-800 px-6 py-3 rounded-2xl border border-gray-700 shadow-inner">
                        {{ $joinUrl }}</p>
                </div>

                <div
                    class="bg-gray-800/80 backdrop-blur-md border border-gray-700 w-[500px] h-[400px] rounded-[3rem] p-8 flex flex-col shadow-2xl">
                    <h2
                        class="text-2xl text-cyan-400 font-black uppercase tracking-widest mb-6 text-center border-b border-gray-700 pb-4">
                        Pemain Terdaftar (<span x-text="players.length"></span>)</h2>
                    <div class="flex flex-wrap gap-3 overflow-y-auto content-start flex-grow pr-2">
                        <template x-for="p in players" :key="p.id">
                            <span
                                class="bg-gray-700 border border-gray-500 text-white font-bold text-lg px-5 py-2 rounded-xl shadow-md"
                                x-text="p.name"></span>
                        </template>
                        <div x-show="players.length === 0" class="w-full text-center text-gray-500 mt-10 italic">
                            Menunggu pemain bergabung...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN GAME CONTENT -->
        <div x-show="!isLobby" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-10 flex-grow">
            <!-- Area Tengah: Arena Utama -->
            <div class="lg:col-span-2 flex flex-col">
                <div class="flex-grow flex flex-col items-center justify-center bg-gray-800 p-8 rounded-[3rem] border border-gray-700 shadow-[0_0_50px_rgba(0,0,0,0.5)] relative overflow-hidden transition-all duration-300"
                    :class="{ 'border-pink-500 shadow-[0_0_80px_rgba(236,72,153,0.6)]': answeringPlayer && !isGameOver }">

                    <div x-show="answeringPlayer" class="absolute inset-0 bg-pink-500/10 animate-pulse"></div>

                    <!-- Podium Pemenang -->
                    <div x-show="isGameOver" x-cloak
                        class="absolute inset-0 z-20 bg-gray-900 flex flex-col items-center justify-center p-10">
                        <div
                            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-yellow-500/20 via-gray-900 to-gray-900 animate-pulse">
                        </div>
                        <h1
                            class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-b from-white to-gray-400 mb-12 relative z-10 uppercase tracking-widest drop-shadow-2xl">
                            SELAMAT KEPADA PEMENANG
                        </h1>
                        <!-- Podium Layout -->
                        <div class="flex items-end justify-center gap-8 relative z-10 w-full max-w-5xl h-80">
                            <!-- Juara 2 -->
                            <div class="flex flex-col items-center justify-end w-1/3 h-[70%]" x-show="players[1]">
                                <h3 class="text-4xl font-bold text-white mb-4 uppercase drop-shadow-md"
                                    x-text="players[1]?.name"></h3>
                                <div
                                    class="w-full bg-gray-700 border-t-4 border-gray-400 rounded-t-3xl h-full flex justify-center items-center">
                                    <span class="text-5xl font-black text-gray-400">#2</span>
                                </div>
                                <div class="mt-4 text-3xl font-mono font-bold text-yellow-400"
                                    x-text="(players[1]?.score || 0) + ' Pts'"></div>
                            </div>
                            <!-- Juara 1 -->
                            <div class="flex flex-col items-center justify-end w-1/3 h-full relative"
                                x-show="players[0]">
                                <div
                                    class="absolute -top-16 text-6xl drop-shadow-[0_0_20px_rgba(250,204,21,1)] animate-bounce">
                                    👑</div>
                                <h3 class="text-5xl font-black text-yellow-400 mb-4 uppercase drop-shadow-lg"
                                    x-text="players[0]?.name"></h3>
                                <div
                                    class="w-full bg-gradient-to-t from-yellow-600 to-yellow-400 rounded-t-3xl h-full flex justify-center items-center">
                                    <span class="text-7xl font-black text-gray-900">#1</span>
                                </div>
                                <div class="mt-4 text-4xl font-mono font-black text-yellow-400 drop-shadow-lg"
                                    x-text="(players[0]?.score || 0) + ' Pts'"></div>
                            </div>
                            <!-- Juara 3 -->
                            <div class="flex flex-col items-center justify-end w-1/3 h-[50%]" x-show="players[2]">
                                <h3 class="text-3xl font-bold text-white mb-4 uppercase drop-shadow-md"
                                    x-text="players[2]?.name"></h3>
                                <div
                                    class="w-full bg-orange-900 border-t-4 border-orange-700 rounded-t-3xl h-full flex justify-center items-center">
                                    <span class="text-4xl font-black text-orange-500">#3</span>
                                </div>
                                <div class="mt-4 text-2xl font-mono font-bold text-yellow-400"
                                    x-text="(players[2]?.score || 0) + ' Pts'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Timer Content -->
                    <div x-show="!isGameOver">
                        <div x-show="!answeringPlayer"
                            class="text-[15rem] font-black font-mono leading-none tracking-tighter transition-colors"
                            :class="timeLeft <= 3 && timeLeft > 0 ? 'text-red-500 drop-shadow-[0_0_40px_rgba(239,68,68,0.8)]' :
                                'text-cyan-400 drop-shadow-[0_0_40px_rgba(34,211,238,0.8)]'"
                            x-text="displayTime"></div>
                        <p x-show="!answeringPlayer"
                            class="text-3xl text-gray-500 mt-4 tracking-widest font-light text-center"
                            x-text="timeLeft > 0 ? 'WAKTU REBUTAN' : 'MENUNGGU SOAL'"></p>

                        <div x-show="answeringPlayer" class="text-center relative z-10 w-full" style="display: none;">
                            <p class="text-4xl text-gray-300 mb-4 tracking-widest">KESEMPATAN MENJAWAB:</p>
                            <h2 class="text-[7rem] font-black text-pink-500 drop-shadow-[0_0_50px_rgba(236,72,153,1)] uppercase leading-none mb-6"
                                x-text="answeringPlayer?.name"></h2>
                            <div class="text-[6rem] font-mono font-bold text-yellow-400 drop-shadow-lg"
                                x-text="answerTimeLeft"></div>
                            <p class="text-2xl text-yellow-500/50 mt-2 tracking-widest">DETIK TERSISA</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Kanan: Leaderboard -->
            <div class="bg-gray-800 p-8 rounded-[3rem] border border-gray-700 shadow-xl flex flex-col relative">
                <h2 class="text-3xl font-black text-cyan-400 mb-8 uppercase tracking-widest text-center">KLASEMEN SKOR
                </h2>

                <div class="relative flex-grow w-full" :style="`min-height: ${players.length * 110}px`">
                    <template x-for="(player, index) in players" :key="player.id">
                        <div class="leaderboard-item absolute w-full flex justify-between items-center backdrop-blur-md px-6 py-4 rounded-2xl border"
                            :class="highlightedPlayers.includes(player.id) ?
                                'bg-green-500/90 border-green-400 shadow-[0_0_40px_rgba(34,197,94,0.6)]' :
                                (wrongPlayers.includes(player.id) ?
                                    'bg-red-600/90 border-red-500 shadow-[0_0_40px_rgba(239,68,68,0.6)]' :
                                    'bg-gray-700/80 border-gray-600 shadow-lg')"
                            :style="`transform: translateY(${index * 110}px) scale(${highlightedPlayers.includes(player.id) ? 1.03 : 1});
                                                                                              z-index: ${highlightedPlayers.includes(player.id) || wrongPlayers.includes(player.id) ? 999 : (100 - index)};`">

                            <div class="flex items-center gap-6">
                                <div class="w-12 h-12 flex items-center justify-center relative">
                                    <template x-if="index === 0">
                                        <div
                                            class="absolute -top-8 text-3xl animate-bounce drop-shadow-[0_0_10px_rgba(250,204,21,0.8)]">
                                            👑</div>
                                    </template>
                                    <span class="text-4xl font-black"
                                        :class="index === 0 ? 'text-yellow-400' : 'text-gray-400'"
                                        x-text="index + 1"></span>
                                </div>
                                <span class="font-bold text-3xl text-white truncate max-w-[170px]"
                                    x-text="player.name"></span>
                            </div>

                            <span
                                class="text-yellow-400 font-black text-3xl font-mono bg-gray-900 px-5 py-3 rounded-xl border border-yellow-500/30 shadow-inner"
                                x-text="player.score"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ENGINE SUARA -->
    <script>
        const AudioEngine = {
            ctx: null,
            init() {
                if (!this.ctx) this.ctx = new(window.AudioContext || window.webkitAudioContext)();
                if (this.ctx.state === 'suspended') this.ctx.resume();
            },
            playTick(isUrgent = false) {
                if (!this.ctx) return;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(isUrgent ? 800 : 400, this.ctx.currentTime);
                gain.gain.setValueAtTime(0, this.ctx.currentTime);
                gain.gain.linearRampToValueAtTime(0.3, this.ctx.currentTime + 0.01);
                gain.gain.linearRampToValueAtTime(0, this.ctx.currentTime + 0.05);
                osc.start();
                osc.stop(this.ctx.currentTime + 0.06);
            },
            playBuzzer() {
                if (!this.ctx) return;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(300, this.ctx.currentTime);
                gain.gain.setValueAtTime(0.5, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.5);
                osc.start();
                osc.stop(this.ctx.currentTime + 0.5);
            },
            playTimeUp() {
                if (!this.ctx) return;
                const osc = this.ctx.createOscillator();
                const gain = this.ctx.createGain();
                osc.connect(gain);
                gain.connect(this.ctx.destination);
                osc.type = 'square';
                osc.frequency.setValueAtTime(150, this.ctx.currentTime);
                gain.gain.setValueAtTime(0.5, this.ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 1);
                osc.start();
                osc.stop(this.ctx.currentTime + 1);
            }
        };

        document.addEventListener('alpine:init', () => {
            Alpine.data('displayScreen', (roomId, initialPlayers, initialLobbyState, timerMenjawab) => ({
                audioEnabled: false,
                players: initialPlayers,
                isLobby: initialLobbyState,
                timeLeft: 0,
                displayTime: '00.0',
                timerInterval: null,
                lastSecondCount: 0,
                answeringPlayer: null,
                answerTimeLeft: timerMenjawab,
                answerTimerInterval: null,
                isGameOver: false,
                highlightedPlayers: [],
                wrongPlayers: [],

                enableAudio() {
                    AudioEngine.init();
                    this.audioEnabled = true;
                },

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'start') {
                                this.isGameOver = false;
                                this.startTimer(new Date(e.timerEndsAt).getTime());
                            } else if (e.action === 'reset') {
                                this.resetGame();
                                this.updateScores();
                            } else if (e.action === 'end_game') {
                                this.isGameOver = true;
                                this.updateScores();
                            } else if (e.action === 'player_joined') {
                                this.updateScores();
                            } else if (e.action === 'lobby_locked') {
                                this.isLobby = false;
                            } else if (e.action === 'lobby_unlocked') {
                                this.isLobby = true;
                            }
                        })
                        .listen('PlayerBuzzed', (e) => {
                            clearInterval(this.timerInterval);
                            this.answeringPlayer = e.player;
                            AudioEngine.playBuzzer();
                            this.startAnswerTimer(timerMenjawab);
                        });
                },

                async updateScores() {
                    let res = await axios.get(`/api/room/neon/players`);
                    let newPlayers = res.data;
                    newPlayers.forEach(newP => {
                        let oldP = this.players.find(p => p.id === newP.id);
                        if (oldP) {
                            if (newP.score > oldP.score) {
                                this.highlightedPlayers.push(newP.id);
                                setTimeout(() => {
                                    this.highlightedPlayers = this
                                        .highlightedPlayers.filter(id => id !== newP
                                            .id);
                                }, 3000);
                            } else if (newP.score < oldP.score) {
                                this.wrongPlayers.push(newP.id);
                                setTimeout(() => {
                                    this.wrongPlayers = this.wrongPlayers.filter(
                                        id => id !== newP.id);
                                }, 3000);
                            }
                        }
                    });
                    this.players = newPlayers;
                },

                startTimer(endsAt) {
                    this.resetGame();
                    this.timerInterval = setInterval(() => {
                        let diff = endsAt - new Date().getTime();
                        if (diff <= 0) {
                            clearInterval(this.timerInterval);
                            this.displayTime = '00.0';
                            this.timeLeft = 0;
                            AudioEngine.playTimeUp();
                        } else {
                            this.timeLeft = diff / 1000;
                            this.displayTime = this.timeLeft.toFixed(1);

                            let currentSecond = Math.ceil(this.timeLeft);
                            if (currentSecond !== this.lastSecondCount && currentSecond > 0) {
                                AudioEngine.playTick(currentSecond <= 3);
                                this.lastSecondCount = currentSecond;
                            }
                        }
                    }, 100);
                },

                startAnswerTimer(duration) {
                    this.answerTimeLeft = duration;
                    clearInterval(this.answerTimerInterval);
                    this.answerTimerInterval = setInterval(() => {
                        if (this.answerTimeLeft > 0) {
                            this.answerTimeLeft--;
                            AudioEngine.playTick(this.answerTimeLeft <= 5);
                        } else {
                            clearInterval(this.answerTimerInterval);
                            AudioEngine.playTimeUp();
                            axios.post(`/api/room/neon/timeout`, {
                                player_id: this.answeringPlayer.id
                            }).catch(() => location.reload());
                        }
                    }, 1000);
                },

                resetGame() {
                    clearInterval(this.timerInterval);
                    clearInterval(this.answerTimerInterval);
                    this.displayTime = '00.0';
                    this.answeringPlayer = null;
                    this.lastSecondCount = 0;
                }
            }));
        });
    </script>
</body>

</html>

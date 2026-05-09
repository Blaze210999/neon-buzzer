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

<body class="bg-gray-950 text-white min-h-screen p-6 relative" x-data="adminPanel({{ $room->id }}, {{ cache('room_locked_' . $room->id) ? 'false' : 'true' }})">

    <div class="max-w-xl mx-auto bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl relative overflow-hidden">

        <!-- Header -->
        <div class="flex justify-between items-center border-b border-gray-800 pb-4 mb-6">
            <a href="{{ route('game.admin.dashboard') }}" class="text-gray-500 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-xl font-bold text-gray-400 uppercase tracking-widest">Remote Mode 1</h1>
            <div class="w-6"></div>
        </div>

        <!-- ================= KONTROL LOBBY ================= -->
        <div x-show="isLobby" class="text-center py-10">
            <div class="mb-8 relative inline-block">
                <div class="absolute inset-0 bg-cyan-500 blur-xl opacity-30 animate-pulse"></div>
                <h2 class="text-4xl font-black text-cyan-400 relative z-10 tracking-widest uppercase">LOBBY TERBUKA</h2>
            </div>
            <p class="text-gray-400 mb-10 text-sm">Pemain masih bisa bergabung. Tunggu semua pemain masuk dan bersiap
                sebelum memulai permainan.</p>

            <button @click="lockLobby"
                class="w-full bg-cyan-600 hover:bg-cyan-500 text-white text-2xl font-black py-8 rounded-3xl shadow-[0_0_30px_rgba(34,211,238,0.4)] transition transform hover:scale-105 uppercase tracking-widest">
                Kunci Lobby & Mulai Game
            </button>
        </div>
        <!-- ================================================= -->

        <!-- ================= KONTROL GAME ================= -->
        <div x-show="!isLobby" style="display: none;">

            <!-- Tombol Kendali Utama -->
            <div x-show="!answeringPlayer" class="flex flex-col gap-4">
                <button @click="control('start')"
                    class="w-full bg-green-600 hover:bg-green-500 text-white text-2xl font-black py-6 rounded-2xl shadow-[0_0_20px_rgba(34,197,94,0.3)] transition transform hover:scale-105">
                    ▶️ MULAI REBUTAN ({{ $room->timer_rebutan }}s)
                </button>
                <button @click="control('reset')"
                    class="w-full bg-gray-700 hover:bg-gray-600 text-white text-lg font-bold py-4 rounded-2xl transition">
                    ⏹️ BATALKAN / RESET
                </button>
            </div>

            <!-- Tombol Penilaian -->
            <div x-show="answeringPlayer" style="display: none;"
                class="text-center bg-gray-800 border border-pink-500/50 p-6 rounded-2xl shadow-[0_0_30px_rgba(236,72,153,0.3)]">
                <p class="text-gray-400 mb-2 text-sm uppercase tracking-widest">Pilih Multiplier Skor:</p>
                <div class="flex justify-center gap-2 mb-6">
                    <template x-for="m in [1, 2, 3]">
                        <button @click="multiplier = m"
                            :class="multiplier === m ? 'bg-pink-500 text-white shadow-[0_0_15px_rgba(236,72,153,0.5)]' :
                                'bg-gray-700 text-gray-400'"
                            class="px-6 py-2 rounded-xl font-bold transition-all" x-text="m + 'x'"></button>
                    </template>
                </div>
                <h2 class="text-3xl font-black text-pink-500 uppercase mb-6" x-text="answeringPlayer?.name"></h2>
                <div class="flex flex-col gap-4">
                    <button @click="gradeAnswer(true)"
                        class="w-full bg-blue-600 hover:bg-blue-500 text-white text-xl font-black py-4 rounded-2xl">✅
                        BENAR (+<span x-text="{{ $room->poin_benar }} * multiplier"></span>)</button>
                    <button @click="gradeAnswer(false)"
                        class="w-full bg-red-600 hover:bg-red-500 text-white text-xl font-black py-4 rounded-2xl">❌
                        SALAH (-<span x-text="{{ $room->poin_salah }} * multiplier"></span>)</button>
                </div>
            </div>

            <!-- Info Pemain Ringkas & Tombol Kick -->
            <div class="mt-8 pt-6 border-t border-gray-800">
                <h3 class="text-sm text-gray-500 mb-4 uppercase">Status Pemain (<span x-text="playersCount"></span>)
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($players as $p)
                        <span
                            class="bg-gray-800 text-sm pl-4 pr-2 py-2 rounded-xl text-gray-300 flex items-center gap-3 border border-gray-700">
                            {{ $p->name }}
                            <span
                                class="bg-gray-900 text-yellow-400 font-bold px-2 py-0.5 rounded-md">{{ $p->score }}</span>
                            <!-- Tombol Kick memicu modal -->
                            <button @click="openKickModal({{ $p->id }}, '{{ addslashes($p->name) }}')"
                                class="text-orange-500 hover:text-white bg-orange-500/10 hover:bg-orange-500 p-1.5 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Area Bawah: Reset, Akhiri Game, & Buka Lobby -->
            <div class="mt-8 pt-6 border-t border-gray-800 flex flex-col gap-3">
                <button @click="unlockLobby"
                    class="w-full py-3 border border-cyan-500/30 text-cyan-500 hover:bg-cyan-500 hover:text-white rounded-xl text-sm font-bold transition-all uppercase tracking-widest mb-4">
                    Buka Kembali Pintu Lobby
                </button>

                <button @click="showResetModal = true"
                    class="w-full py-3 border border-red-500/30 text-red-500 hover:bg-red-500 hover:text-white rounded-xl text-sm font-bold transition-all uppercase tracking-widest">
                    Reset Semua Skor Pemain
                </button>

                <button @click="confirmEndGame"
                    class="w-full py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-400 hover:to-yellow-500 text-black rounded-xl font-black text-lg shadow-[0_0_20px_rgba(234,179,8,0.4)] transition-all uppercase tracking-widest">
                    🏆 Akhiri Game & Tampilkan Pemenang
                </button>
            </div>
        </div>
        <!-- ================================================ -->
    </div>

    <!-- MODAL RESET SKOR -->
    <div x-show="showResetModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showResetModal = false"
            class="bg-gray-900 border border-red-500 p-8 rounded-3xl shadow-[0_0_50px_rgba(220,38,38,0.4)] max-w-sm w-full text-center">
            <div class="text-red-500 mb-6 flex justify-center"><svg class="w-20 h-20" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg></div>
            <h3 class="text-2xl font-black text-white mb-2 uppercase">Yakin Reset Skor?</h3>
            <p class="text-gray-400 mb-8 text-sm">Semua tim akan kembali memiliki 0 poin.</p>
            <div class="flex gap-4">
                <button @click="showResetModal = false"
                    class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl">Batal</button>
                <button @click="executeResetScores" class="flex-1 bg-red-600 text-white font-bold py-3 rounded-xl">Ya,
                    Reset!</button>
            </div>
        </div>
    </div>

    <!-- MODAL KICK PEMAIN -->
    <div x-show="showKickModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showKickModal = false"
            class="bg-gray-900 border border-orange-500 p-8 rounded-3xl shadow-[0_0_50px_rgba(249,115,22,0.4)] max-w-sm w-full text-center">
            <div class="text-orange-500 mb-6 flex justify-center"><svg class="w-20 h-20" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg></div>
            <h3 class="text-xl font-black text-white mb-2 uppercase">Tendang Pemain?</h3>
            <p class="text-gray-400 mb-6 text-sm">Tim <span class="font-bold text-orange-400"
                    x-text="playerToKickName"></span> akan dihapus dari permainan ini.</p>
            <div class="flex gap-4">
                <button @click="showKickModal = false"
                    class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl">Batal</button>
                <button @click="executeKickPlayer"
                    class="flex-1 bg-orange-600 text-white font-bold py-3 rounded-xl shadow-[0_0_15px_rgba(249,115,22,0.5)]">Ya,
                    Hapus</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminPanel', (roomId, initialLobby) => ({
                isLobby: initialLobby,
                answeringPlayer: null,
                multiplier: 1,
                showResetModal: false,
                showKickModal: false,
                playerToKickId: null,
                playerToKickName: '',
                playersCount: {{ $players->count() }},

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'reset') {
                                this.answeringPlayer = null;
                                this.multiplier = 1;
                            } else if (e.action === 'player_joined') {
                                location.reload();
                            } else if (e.action === 'lobby_locked') {
                                this.isLobby = false;
                            } else if (e.action === 'lobby_unlocked') {
                                this.isLobby = true;
                            }
                        })
                        .listen('PlayerBuzzed', (e) => {
                            this.answeringPlayer = e.player;
                        });
                },

                lockLobby() {
                    axios.post(`/api/room/neon/lock-lobby`);
                },
                unlockLobby() {
                    if (confirm('Buka kunci? Pemain baru akan bisa bergabung lagi.')) axios.post(
                        `/api/room/neon/unlock-lobby`);
                },

                control(action) {
                    axios.post(`/api/room/neon/control`, {
                        action: action,
                        duration: {{ $room->timer_rebutan }}
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
                },

                executeResetScores() {
                    axios.post(`/api/room/neon/reset-scores`).then(() => {
                        this.showResetModal = false;
                        location.reload();
                    });
                },

                openKickModal(id, name) {
                    this.playerToKickId = id;
                    this.playerToKickName = name;
                    this.showKickModal = true;
                },

                executeKickPlayer() {
                    axios.delete(`/api/room/neon/player/${this.playerToKickId}`).then(() => {
                        this.showKickModal = false;
                        location.reload();
                    });
                },

                confirmEndGame() {
                    if (confirm('Kunci permainan dan tampilkan Pemenang di Layar Utama?')) {
                        axios.post(`/api/room/neon/end-game`).then(() => {
                            alert("Game Selesai! Cek Layar Proyektor!");
                        });
                    }
                }
            }));
        });
    </script>
</body>

</html>

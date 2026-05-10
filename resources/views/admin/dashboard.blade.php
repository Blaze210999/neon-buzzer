<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kontrol Pusat</title>
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

<body class="bg-gray-950 text-white min-h-screen p-6 relative" x-data="masterControl({{ $room->id }}, {{ cache('room_locked_' . $room->id) ? 'false' : 'true' }})">

    <div class="max-w-3xl mx-auto bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">
        <h1
            class="text-4xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mb-8 uppercase tracking-widest">
            KONTROL PUSAT
        </h1>

        <div class="mb-8 p-6 rounded-2xl border transition-all"
            :class="isLobby ? 'bg-cyan-900/20 border-cyan-500 shadow-[0_0_20px_rgba(34,211,238,0.2)]' :
                'bg-gray-800 border-red-500/50'">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-sm text-gray-400 uppercase tracking-widest mb-1 font-bold">Status Pintu Masuk</h2>
                    <p class="text-2xl font-black uppercase tracking-widest"
                        :class="isLobby ? 'text-cyan-400' : 'text-red-500'"
                        x-text="isLobby ? '🔓 LOBBY TERBUKA' : '🔒 LOBBY TERKUNCI'"></p>
                </div>
                <button x-show="isLobby" @click="lockLobby"
                    class="bg-cyan-600 hover:bg-cyan-500 text-white px-6 py-4 rounded-xl font-bold uppercase tracking-widest shadow-[0_0_15px_rgba(34,211,238,0.4)] transition hover:scale-105">
                    Kunci Lobby
                </button>
                <button x-show="!isLobby" @click="unlockLobby"
                    class="bg-gray-700 hover:bg-gray-600 text-white border border-gray-500 px-6 py-4 rounded-xl font-bold uppercase tracking-widest transition">
                    Buka Lobby
                </button>
            </div>
        </div>

        <h3 class="text-sm text-gray-500 mb-4 uppercase tracking-widest font-bold border-b border-gray-800 pb-2">Menu &
            Mode Permainan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <a href="{{ route('game.admin.control') }}"
                class="group bg-gray-800 border border-gray-700 hover:border-pink-500 rounded-2xl p-5 transition shadow-lg flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white group-hover:text-pink-400 mb-1">🎯 Mode 1: Rebutan</h2>
                    <p class="text-gray-400 text-xs">Kuis cepat standar.</p>
                </div>
                <div
                    class="bg-pink-500/20 text-pink-500 p-3 rounded-full group-hover:bg-pink-500 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg></div>
            </a>

            <a href="{{ route('game.admin.mode2') }}"
                class="group bg-gray-800 border border-gray-700 hover:border-purple-500 rounded-2xl p-5 transition shadow-lg flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white group-hover:text-purple-400 mb-1">🎵 Mode 2: Tebak Lagu</h2>
                    <p class="text-gray-400 text-xs">Pemutar playlist YouTube.</p>
                </div>
                <div
                    class="bg-purple-500/20 text-purple-500 p-3 rounded-full group-hover:bg-purple-500 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                        </path>
                    </svg></div>
            </a>

            <a href="{{ route('game.admin.settings') }}"
                class="group bg-gray-800 border border-gray-700 hover:border-cyan-500 rounded-2xl p-5 transition shadow-lg flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-300 group-hover:text-cyan-400 mb-1">⚙️ Pengaturan</h2>
                </div>
            </a>

            <a href="{{ route('game.admin.logs') }}"
                class="group bg-gray-800 border border-gray-700 hover:border-blue-500 rounded-2xl p-5 transition shadow-lg flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-300 group-hover:text-blue-400 mb-1">🕵️‍♂️ Audit Log (VAR)
                    </h2>
                </div>
            </a>
        </div>

        <h3 class="text-sm text-gray-500 mb-4 uppercase tracking-widest font-bold border-b border-gray-800 pb-2">
            Manajemen Pemain ({{ $players->count() }})</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
            @foreach ($players as $p)
                <div
                    class="bg-gray-800 flex justify-between items-center px-4 py-3 rounded-xl border border-gray-700 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-white font-bold">{{ $p->name }}</span>
                        <span
                            class="bg-gray-900 text-yellow-400 font-black px-2 py-1 rounded-md text-xs border border-yellow-500/20">{{ $p->score }}
                            Pts</span>
                    </div>
                    <button @click="openKickModal({{ $p->id }}, '{{ addslashes($p->name) }}')"
                        class="text-red-500 hover:text-white bg-red-500/10 hover:bg-red-500 p-2 rounded-lg transition"
                        title="Hapus Tim Ini">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            @endforeach
            @if ($players->count() == 0)
                <p class="text-gray-600 italic text-sm">Belum ada pemain bergabung.</p>
            @endif
        </div>

        <h3 class="text-sm text-red-500/50 mb-4 uppercase tracking-widest font-bold border-b border-gray-800 pb-2">
            Kontrol Lanjutan</h3>
        <div class="flex flex-col gap-3">
            <button @click="showResetModal = true"
                class="w-full py-4 border border-red-500/30 bg-red-500/5 text-red-500 hover:bg-red-500 hover:text-white rounded-xl font-bold transition-all uppercase tracking-widest">
                🔄 Reset Semua Skor (Jadi 0)
            </button>
            <button @click="confirmEndGame"
                class="w-full py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-400 hover:to-yellow-500 text-black rounded-xl font-black text-lg shadow-[0_0_20px_rgba(234,179,8,0.4)] transition-all uppercase tracking-widest">
                🏆 Akhiri Game & Tampilkan Podium
            </button>
        </div>
    </div>

    <div x-show="showKickModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showKickModal = false"
            class="bg-gray-900 border border-red-500 p-8 rounded-3xl max-w-sm w-full text-center">
            <h3 class="text-xl font-black text-white mb-2 uppercase">Hapus Tim?</h3>
            <p class="text-gray-400 mb-6 text-sm">Tim <span class="font-bold text-red-400"
                    x-text="playerToKickName"></span> akan dihapus.</p>
            <div class="flex gap-4">
                <button @click="showKickModal = false"
                    class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl">Batal</button>
                <button @click="executeKickPlayer" class="flex-1 bg-red-600 text-white font-bold py-3 rounded-xl">Ya,
                    Hapus</button>
            </div>
        </div>
    </div>

    <div x-show="showResetModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showResetModal = false"
            class="bg-gray-900 border border-red-500 p-8 rounded-3xl max-w-sm w-full text-center">
            <h3 class="text-2xl font-black text-white mb-2 uppercase">Yakin Reset Skor?</h3>
            <div class="flex gap-4 mt-6">
                <button @click="showResetModal = false"
                    class="flex-1 bg-gray-800 text-white font-bold py-3 rounded-xl">Batal</button>
                <button @click="executeResetScores" class="flex-1 bg-red-600 text-white font-bold py-3 rounded-xl">Ya,
                    Reset!</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('masterControl', (roomId, initialLobby) => ({
                isLobby: initialLobby,
                showKickModal: false,
                showResetModal: false,
                playerToKickId: null,
                playerToKickName: '',
                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'player_joined') location.reload();
                            else if (e.action === 'lobby_locked') this.isLobby = false;
                            else if (e.action === 'lobby_unlocked') this.isLobby = true;
                        });
                },
                lockLobby() {
                    axios.post(`/api/room/neon/lock-lobby`);
                },
                unlockLobby() {
                    axios.post(`/api/room/neon/unlock-lobby`);
                },
                openKickModal(id, name) {
                    this.playerToKickId = id;
                    this.playerToKickName = name;
                    this.showKickModal = true;
                },
                executeKickPlayer() {
                    axios.delete(`/api/room/neon/player/${this.playerToKickId}`).then(() => location
                        .reload());
                },
                executeResetScores() {
                    axios.post(`/api/room/neon/reset-scores`).then(() => location.reload());
                },
                confirmEndGame() {
                    if (confirm('Tampilkan Pemenang di Layar Utama?')) axios.post(
                        `/api/room/neon/end-game`);
                }
            }));
        });
    </script>
</body>

</html>

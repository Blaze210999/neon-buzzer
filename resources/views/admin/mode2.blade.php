<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Playlist Kuis Lagu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
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

<body class="bg-gray-950 text-white min-h-screen p-6" x-data="mode2Panel({{ $room->id }}, {{ $room->m2_timer_rebutan }}, '{{ $room->m2_timer_start }}')">

    <div class="fixed top-0 left-0 w-1 h-1 opacity-0 pointer-events-none -z-50">
        <div id="ytplayer"></div>
    </div>

    <div class="max-w-5xl mx-auto bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-2xl overflow-hidden">

        <div class="flex items-center gap-4 border-b border-gray-800 pb-4 mb-6">
            <a href="{{ route('game.admin.dashboard') }}"
                class="text-gray-500 hover:text-white transition bg-gray-800 p-2 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1
                class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500 uppercase tracking-widest">
                🎵 MODE 2: TEBAK LAGU (PLAYLIST)
            </h1>
        </div>

        <div x-show="!isPlaylistLoaded" class="py-10 text-center max-w-2xl mx-auto">
            <div class="mb-8">
                <svg class="w-24 h-24 mx-auto text-purple-500 mb-4 drop-shadow-[0_0_15px_rgba(168,85,247,0.5)]"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3">
                    </path>
                </svg>
                <h2 class="text-3xl font-black text-white uppercase tracking-widest mb-2">Muat Playlist YouTube</h2>
                <p class="text-gray-400">Masukkan link playlist dari YouTube atau YouTube Music.</p>
            </div>
            <input type="text" x-model="playlistUrl" placeholder="https://music.youtube.com/playlist?list=PL..."
                class="w-full bg-gray-800 text-white text-center rounded-xl border border-gray-700 px-6 py-4 outline-none mb-6 focus:border-purple-500 transition text-lg font-mono">
            <button @click="loadPlaylist"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-xl font-black py-4 rounded-xl shadow-[0_0_20px_rgba(168,85,247,0.4)] transition transform hover:scale-105 uppercase tracking-widest">
                ▶️ LOAD PLAYLIST SEKARANG
            </button>
        </div>

        <div x-show="isPlaylistLoaded" style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div
                class="flex flex-col items-center justify-center p-6 bg-gray-950/50 rounded-3xl border border-gray-800 shadow-inner relative">
                <div
                    class="w-64 h-64 md:w-80 md:h-80 rounded-2xl overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.8)] border border-gray-700 relative group bg-gray-900 flex items-center justify-center">
                    <template x-if="currentVideoId"><img
                            :src="'https://img.youtube.com/vi/' + currentVideoId + '/hqdefault.jpg'"
                            class="w-full h-full object-cover transform transition duration-700 group-hover:scale-110"></template>
                    <div x-show="!currentVideoId" class="text-gray-500 font-bold animate-pulse">Menarik Data...</div>
                </div>
                <div class="mt-8 text-center w-full px-4">
                    <p class="text-purple-400 text-xs font-bold uppercase tracking-[0.2em] mb-2">Daftar Putar</p>
                    <h3 class="text-2xl font-black text-white truncate drop-shadow-md" x-text="currentTitle"></h3>
                </div>
                <div x-show="!answeringPlayer" class="flex items-center gap-6 mt-6">
                    <button @click="prevSong"
                        class="p-4 bg-gray-800 hover:bg-gray-700 hover:text-purple-400 rounded-full transition shadow-lg border border-gray-700"><svg
                            class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"></path>
                        </svg></button>
                    <button @click="nextSong"
                        class="p-4 bg-gray-800 hover:bg-gray-700 hover:text-purple-400 rounded-full transition shadow-lg border border-gray-700"><svg
                            class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"></path>
                        </svg></button>
                </div>
            </div>

            <div class="flex flex-col justify-center">
                <div x-show="!answeringPlayer">
                    <h3
                        class="text-gray-500 text-xs font-bold mb-4 uppercase tracking-[0.2em] border-b border-gray-800 pb-2">
                        Pilih Mode Pemutaran</h3>
                    <div class="flex flex-col gap-4 mb-4">
                        <button @click="playRound(2, 200)"
                            class="bg-gray-800 hover:bg-gray-700 border border-gray-600 hover:border-purple-500 py-4 px-6 rounded-2xl font-bold transition shadow-md flex justify-between items-center group">
                            <span class="text-purple-400 group-hover:text-purple-300">▶️ RONDE 1 (2 Detik)</span><span
                                class="bg-gray-900 text-white px-3 py-1 rounded-lg text-sm">200 Pts</span>
                        </button>
                        <button @click="playRound(5, 150)"
                            class="bg-gray-800 hover:bg-gray-700 border border-gray-600 hover:border-pink-500 py-4 px-6 rounded-2xl font-bold transition shadow-md flex justify-between items-center group">
                            <span class="text-pink-400 group-hover:text-pink-300">▶️ RONDE 2 (5 Detik)</span><span
                                class="bg-gray-900 text-white px-3 py-1 rounded-lg text-sm">150 Pts</span>
                        </button>
                        <button @click="playRound(10, 100)"
                            class="bg-gray-800 hover:bg-gray-700 border border-gray-600 hover:border-cyan-500 py-4 px-6 rounded-2xl font-bold transition shadow-md flex justify-between items-center group">
                            <span class="text-cyan-400 group-hover:text-cyan-300">▶️ RONDE 3 (10 Detik)</span><span
                                class="bg-gray-900 text-white px-3 py-1 rounded-lg text-sm">100 Pts</span>
                        </button>
                    </div>
                    <button @click="stopMusicAndReset"
                        class="w-full bg-gray-800 border border-gray-700 hover:bg-gray-700 text-gray-400 hover:text-white text-lg font-bold py-4 rounded-2xl transition mt-4">
                        ⏹️ HENTIKAN MUSIK / RESET BEL
                    </button>
                </div>

                <div x-show="answeringPlayer" style="display: none;"
                    class="bg-gray-800 border-2 border-purple-500 p-8 rounded-3xl shadow-[0_0_40px_rgba(168,85,247,0.3)] text-center animate-pulse">
                    <p class="text-gray-400 mb-2 text-xs uppercase tracking-[0.2em] font-bold">Menjawab untuk <span
                            class="text-white" x-text="currentPoints + ' Pts'"></span></p>
                    <h2 class="text-4xl font-black text-purple-400 uppercase mb-8 drop-shadow-md"
                        x-text="answeringPlayer?.name"></h2>
                    <div class="flex flex-col gap-4">
                        <button @click="gradeAnswer(true)"
                            class="w-full bg-green-600 hover:bg-green-500 text-white text-xl font-black py-5 rounded-2xl shadow-lg transition transform hover:scale-105">✅
                            BENAR (+<span x-text="currentPoints"></span>)</button>
                        <button @click="gradeAnswer(false)"
                            class="w-full bg-red-600 hover:bg-red-500 text-white text-xl font-black py-5 rounded-2xl shadow-lg transition transform hover:scale-105">❌
                            SALAH</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mode2Panel', (roomId, timerRebutanDb, timerStartMode) => ({
                timerRebutan: timerRebutanDb,
                timerStartMode: timerStartMode,
                pendingBuzzer: false,
                playlistUrl: '',
                isPlaylistLoaded: false,
                currentVideoId: '',
                currentTitle: '',
                currentPoints: 0,
                answeringPlayer: null,
                targetDuration: 0,
                playTimeout: null,

                init() {
                    window.Echo.channel(`room.${roomId}`)
                        .listen('GameStateChanged', (e) => {
                            if (e.action === 'reset') this.answeringPlayer = null;
                        })
                        .listen('PlayerBuzzed', (e) => {
                            this.answeringPlayer = e.player;
                            if (window.ytPlayer && typeof window.ytPlayer.pauseVideo === 'function')
                                window.ytPlayer.pauseVideo();
                        });

                    const initYT = () => {
                        window.ytPlayer = new YT.Player('ytplayer', {
                            height: '1',
                            width: '1',
                            playerVars: {
                                'autoplay': 1,
                                'controls': 0,
                                'disablekb': 1
                            },
                            events: {
                                'onStateChange': this.onPlayerStateChange.bind(this),
                                'onError': this.onPlayerError.bind(this)
                            }
                        });
                    };
                    if (window.YT && window.YT.Player) {
                        initYT();
                    } else {
                        window.onYouTubeIframeAPIReady = initYT;
                    }
                },

                onPlayerError(event) {
                    let shouldSkip = (event.data == 100 || event.data == 101 || event.data == 150);
                    if (shouldSkip) {
                        this.currentTitle = "Lagu diblokir label. Auto-skip ke lagu berikutnya...";
                        setTimeout(() => {
                            this.nextSong();
                        }, 1500);
                    }
                },

                extractPlaylistId(url) {
                    let match = url.match(/[?&]list=([^&]+)/);
                    return match ? match[1] : null;
                },

                loadPlaylist() {
                    let listId = this.extractPlaylistId(this.playlistUrl);
                    if (!listId) return alert(
                        "Oops! Link tidak valid. Pastikan ada '?list=...' di link.");
                    this.isPlaylistLoaded = true;
                    this.targetDuration = 0;
                    this.currentTitle = "Memancing data dari YouTube...";
                    this.currentVideoId = '';
                    window.ytPlayer.mute();
                    window.ytPlayer.loadPlaylist({
                        list: listId,
                        listType: 'playlist',
                        index: 0
                    });
                },

                onPlayerStateChange(event) {
                    if (window.ytPlayer && window.ytPlayer.getVideoData) {
                        let data = window.ytPlayer.getVideoData();
                        if (data && data.video_id) {
                            this.currentVideoId = data.video_id;
                            this.currentTitle = data.title || "Lagu Siap Dimainkan";
                        }
                    }

                    if (event.data == YT.PlayerState.PLAYING) {
                        if (this.targetDuration === 0) {
                            window.ytPlayer.pauseVideo();
                            window.ytPlayer.seekTo(0);
                        } else {
                            clearTimeout(this.playTimeout);
                            this.playTimeout = setTimeout(() => {
                                window.ytPlayer.pauseVideo();
                                this.targetDuration = 0;

                                // LOGIKA PENDING BUZZER (JIKA START AFTER SONG)
                                if (this.pendingBuzzer) {
                                    axios.post(`/api/room/neon/control`, {
                                        action: 'start',
                                        duration: this.timerRebutan,
                                        mode: 'mode2'
                                    });
                                    this.pendingBuzzer = false;
                                }
                            }, this.targetDuration * 1000);
                        }
                    }
                },

                nextSong() {
                    this.targetDuration = 0;
                    this.currentTitle = "Menarik data lagu berikutnya...";
                    this.currentVideoId = '';
                    window.ytPlayer.mute();
                    window.ytPlayer.nextVideo();
                    setTimeout(() => {
                        window.ytPlayer.playVideo();
                    }, 500);
                },
                prevSong() {
                    this.targetDuration = 0;
                    this.currentTitle = "Menarik data lagu sebelumnya...";
                    this.currentVideoId = '';
                    window.ytPlayer.mute();
                    window.ytPlayer.previousVideo();
                    setTimeout(() => {
                        window.ytPlayer.playVideo();
                    }, 500);
                },

                playRound(duration, points) {
                    if (!this.currentVideoId) return alert(
                    "Tunggu sebentar, data lagu sedang ditarik!");
                    this.currentPoints = points;
                    this.targetDuration = duration;

                    // CEK MODE START TIMER
                    if (this.timerStartMode === 'during') {
                        axios.post(`/api/room/neon/control`, {
                            action: 'start',
                            duration: this.timerRebutan,
                            mode: 'mode2'
                        });
                    } else {
                        this.pendingBuzzer = true;
                    }

                    window.ytPlayer.unMute();
                    window.ytPlayer.setVolume(100);
                    window.ytPlayer.seekTo(0);
                    window.ytPlayer.playVideo();
                },

                stopMusicAndReset() {
                    this.targetDuration = 0;
                    this.pendingBuzzer = false;
                    if (window.ytPlayer && typeof window.ytPlayer.pauseVideo === 'function') window
                        .ytPlayer.pauseVideo();
                    axios.post(`/api/room/neon/control`, {
                        action: 'reset'
                    });
                },

                gradeAnswer(isCorrect) {
                    if (!this.answeringPlayer) return;
                    axios.post(`/api/room/neon/grade`, {
                        player_id: this.answeringPlayer.id,
                        is_correct: isCorrect,
                        multiplier: 1,
                        custom_points: this.currentPoints
                    }).then(() => {
                        this.answeringPlayer = null;
                    });
                }
            }));
        });
    </script>
</body>

</html>

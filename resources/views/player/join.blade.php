<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Join Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>

<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-6">
    <div class="bg-gray-800 p-8 rounded-3xl w-full max-w-md border border-gray-700 shadow-[0_0_50px_rgba(0,0,0,0.5)]">
        <h1
            class="text-3xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mb-8">
            JOIN ROOM NEON</h1>

        <form action="{{ route('player.join', $room->code) }}" method="POST" class="flex flex-col gap-6">
            @csrf
            <div>
                <label class="block text-gray-400 mb-2 font-semibold tracking-wider">NAMA TIM / PEMAIN</label>
                <input type="text" name="name" required autocomplete="off"
                    class="w-full bg-gray-900 border border-gray-700 text-white text-2xl text-center font-bold rounded-2xl p-4 focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/50 transition-all uppercase placeholder-gray-600"
                    placeholder="Misal: TIM A">
            </div>

            <button type="submit"
                class="w-full bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-black text-xl py-4 rounded-2xl shadow-[0_0_20px_rgba(34,211,238,0.4)] transition-all transform hover:scale-105 active:scale-95">
                MASUK SEKARANG!
            </button>
        </form>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="id" class="dark">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - VAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-white min-h-screen p-6">

    <div class="max-w-5xl mx-auto bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8 border-b border-gray-800 pb-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('game.admin.dashboard') }}"
                    class="text-gray-500 hover:text-white transition bg-gray-800 p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1
                    class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500 uppercase tracking-widest">
                    🕵️‍♂️ AUDIT LOG (VAR)
                </h1>
            </div>
            <div class="text-gray-500 text-sm font-mono uppercase tracking-widest">
                Total Record: {{ $logs->count() }}
            </div>
        </div>

        <!-- Tabel Log -->
        <div class="overflow-x-auto rounded-2xl border border-gray-800">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase text-gray-400 tracking-widest">
                    <tr>
                        <th class="px-6 py-4 font-bold">Waktu</th>
                        <th class="px-6 py-4 font-bold">Pemain</th>
                        <th class="px-6 py-4 font-bold">Aksi</th>
                        <th class="px-6 py-4 font-bold">Detail (Payload)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-800/50 transition">
                            <!-- Waktu Kejadian -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-500">
                                {{ $log->created_at->format('d M Y - H:i:s') }}
                            </td>

                            <!-- Nama Pemain -->
                            <td class="px-6 py-4 text-white font-bold whitespace-nowrap">
                                {{ $log->player->name ?? 'Sistem / Terhapus' }}
                            </td>

                            <!-- Badge Aksi -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $bgClass = 'bg-gray-500/20 text-gray-400 border-gray-500'; // Default
                                    if ($log->action === 'buzz') {
                                        $bgClass =
                                            'bg-yellow-500/20 text-yellow-400 border-yellow-500/50 shadow-[0_0_10px_rgba(234,179,8,0.2)]';
                                    }
                                    if ($log->action === 'answer') {
                                        $bgClass =
                                            $log->payload['is_correct'] ?? false
                                                ? 'bg-green-500/20 text-green-400 border-green-500/50 shadow-[0_0_10px_rgba(34,197,94,0.2)]'
                                                : 'bg-red-500/20 text-red-400 border-red-500/50 shadow-[0_0_10px_rgba(239,68,68,0.2)]';
                                    }
                                    if ($log->action === 'penalty') {
                                        $bgClass = 'bg-orange-500/20 text-orange-400 border-orange-500/50';
                                    }
                                    if ($log->action === 'join' || $log->action === 'reconnect') {
                                        $bgClass = 'bg-cyan-500/20 text-cyan-400 border-cyan-500/50';
                                    }
                                @endphp
                                <span
                                    class="px-3 py-1 rounded-md border {{ $bgClass }} text-xs font-black uppercase tracking-widest">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Payload Data (JSON) -->
                            <td class="px-6 py-4">
                                <pre class="text-[11px] font-mono bg-gray-950 p-3 rounded-lg text-cyan-300 border border-gray-800 overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-6 py-10 text-center text-gray-600 font-bold uppercase tracking-widest">
                                Belum ada log aktivitas yang terekam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>

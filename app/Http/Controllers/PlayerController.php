<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Player;
use App\Events\PlayerBuzzed;
use App\Events\GameStateChanged;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class PlayerController extends Controller
{
    // 1. TAMPILKAN FORM JOIN
    public function joinForm(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        // CEK 1 DEVICE 1 PEMAIN: Ambil ID pemain dari Cookie atau Session
        $playerId = $request->cookie('player_device_' . $room->id) ?? session('player_id');

        if ($playerId) {
            // Pastikan pemain ini memang masih ada di database (Belum di-Kick Admin)
            $existingPlayer = Player::where('id', $playerId)->where('room_id', $room->id)->first();
            if ($existingPlayer) {
                // Kunci! Langsung lempar ke dalam arena, jangan kasih form lagi.
                session(['player_id' => $existingPlayer->id]);
                return redirect()->route('player.play');
            }
        }

        if (cache('room_locked_' . $room->id)) {
            return view('player.locked');
        }

        return view('player.join', compact('room'));
    }

    // 2. PROSES PENDAFTARAN
    public function join(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        if (cache('room_locked_' . $room->id)) {
            return view('player.locked');
        }

        $request->validate(['name' => 'required|string|max:20']);

        // Buat ID permanen untuk HP/Browser ini (agar tidak bisa buka tab ganda)
        $deviceId = $request->cookie('device_id') ?? Str::uuid()->toString();

        $isReconnect = Player::where('session_id', $deviceId)->where('room_id', $room->id)->exists();

        // Buat atau Update data pemain berdasarkan Device ID-nya
        $player = Player::updateOrCreate(
            ['session_id' => $deviceId, 'room_id' => $room->id],
            ['name' => $request->name]
        );

        session(['player_id' => $player->id]);

        \App\Models\GameLog::create([
            'room_id' => $room->id,
            'player_id' => $player->id,
            'action' => $isReconnect ? 'reconnect' : 'join',
            'payload' => ['ip_address' => $request->ip(), 'device_id' => $deviceId]
        ]);

        broadcast(new GameStateChanged($room->id, 'player_joined'));

        // TANAMKAN COOKIE: Tandai HP ini selama 12 Jam
        Cookie::queue('device_id', $deviceId, 60 * 12);
        Cookie::queue('player_device_' . $room->id, $player->id, 60 * 12);

        return redirect()->route('player.play');
    }

    // 3. HALAMAN TOMBOL BEL UTAMA
    public function play(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        // Cari siapa yang sedang main di HP ini
        $playerId = session('player_id') ?? $request->cookie('player_device_' . $room->id);
        $player = Player::where('id', $playerId)->where('room_id', $room->id)->first();

        // Jika dia mencoba masuk tapi tidak ada datanya (mungkin sudah di-kick Admin)
        if (!$player) {
            // Hapus cookie bekas dan tendang balik ke halaman awal
            return redirect()->route('player.joinForm')->withCookies([
                Cookie::forget('player_device_' . $room->id)
            ]);
        }

        // Perpanjang masa hidup session agar tidak putus di tengah kuis
        session(['player_id' => $player->id]);

        return view('player.play', compact('room', 'player'));
    }

    // 4. LOGIKA PENCET BEL
    public function buzz(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        $player = Player::findOrFail($request->player_id);

        $affectedRows = Room::where('id', $room->id)
            ->where('status', 'playing')
            ->update(['status' => 'locked']);

        if ($affectedRows > 0) {
            \App\Models\GameLog::create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'action' => 'buzz',
                'payload' => ['reaction_time_ms' => $request->reaction_time ?? 0]
            ]);

            broadcast(new PlayerBuzzed($player, $request->reaction_time ?? 0));
            broadcast(new GameStateChanged($room->id, 'locked'));
            return response()->json(['status' => 'winner']);
        }
        return response()->json(['status' => 'late']);
    }
}

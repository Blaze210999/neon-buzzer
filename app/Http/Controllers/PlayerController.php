<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Player;
use App\Models\Buzz;
use App\Events\PlayerBuzzed;
use App\Events\GameStateChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // PENTING: Jangan sampai ketinggalan

class PlayerController extends Controller
{
    public function joinForm($code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        // CEK APAKAH LOBBY DIKUNCI
        if (cache('room_locked_' . $room->id)) {
            return view('player.locked');
        }

        return view('player.join', compact('room'));
    }

    public function join(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        // CEK SEKALI LAGI SAAT SUBMIT NAMA
        if (cache('room_locked_' . $room->id)) {
            return view('player.locked');
        }

        $request->validate(['name' => 'required|string|max:20']);
        $player = Player::updateOrCreate(
            ['session_id' => session()->getId(), 'room_id' => $room->id],
            ['name' => $request->name]
        );
        session(['player_id' => $player->id]);

        // Beri sinyal ke layar proyektor bahwa ada pemain baru masuk!
        broadcast(new GameStateChanged($room->id, 'player_joined'));

        return redirect()->route('player.play');
    }

    public function play($code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        $player = Player::findOrFail(session('player_id'));
        return view('player.buzzer', compact('room', 'player'));
    }

    public function buzz(Request $request, $code)
    {
        // Cari room dan player
        $room = Room::where('code', $code)->firstOrFail();
        $player = Player::findOrFail($request->player_id);

        // ATOMIC UPDATE: Trik jitu mencegah Race Condition!
        // Kita paksa database mengubah status HANYA JIKA status saat ini adalah 'playing'.
        // Query ini akan mengembalikan angka 1 (jika berhasil) atau 0 (jika gagal/keduluan).
        $affectedRows = Room::where('id', $room->id)
            ->where('status', 'playing')
            ->update(['status' => 'locked']);

        // Jika berhasil diubah (berarti dia yang tercepat)
        if ($affectedRows > 0) {

            \App\Models\Buzz::create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'reaction_time_ms' => $request->reaction_time ?? 0
            ]);

            // Kirim sinyal ke Reverb bahwa ada yang menang
            broadcast(new PlayerBuzzed($player, $request->reaction_time ?? 0));
            broadcast(new GameStateChanged($room->id, 'locked'));

            return response()->json(['status' => 'winner']);
        }

        // Jika affectedRows 0, berarti dia telat atau game sedang tidak dalam status 'playing'
        return response()->json(['status' => 'late']);
    }
}

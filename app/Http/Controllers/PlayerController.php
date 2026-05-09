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
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        if (cache('room_locked_' . $room->id)) {
            return view('player.locked');
        }

        $request->validate(['name' => 'required|string|max:20']);

        // Cek apakah pemain ini orang baru atau reconnect
        $isReconnect = \App\Models\Player::where('session_id', session()->getId())
            ->where('room_id', $room->id)->exists();

        $player = \App\Models\Player::updateOrCreate(
            ['session_id' => session()->getId(), 'room_id' => $room->id],
            ['name' => $request->name]
        );
        session(['player_id' => $player->id]);

        // CATAT KE AUDIT LOG
        \App\Models\GameLog::create([
            'room_id' => $room->id,
            'player_id' => $player->id,
            'action' => $isReconnect ? 'reconnect' : 'join',
            'payload' => ['ip_address' => $request->ip()]
        ]);

        broadcast(new \App\Events\GameStateChanged($room->id, 'player_joined'));
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
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $player = \App\Models\Player::findOrFail($request->player_id);

        $affectedRows = \App\Models\Room::where('id', $room->id)
            ->where('status', 'playing')
            ->update(['status' => 'locked']);

        if ($affectedRows > 0) {
            // CATAT KE AUDIT LOG
            \App\Models\GameLog::create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'action' => 'buzz',
                'payload' => ['reaction_time_ms' => $request->reaction_time ?? 0]
            ]);

            broadcast(new \App\Events\PlayerBuzzed($player, $request->reaction_time ?? 0));
            broadcast(new \App\Events\GameStateChanged($room->id, 'locked'));
            return response()->json(['status' => 'winner']);
        }
        return response()->json(['status' => 'late']);
    }
}

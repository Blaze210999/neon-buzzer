<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Player;
use App\Models\Buzz;
use App\Events\PlayerBuzzed;
use App\Events\GameStateChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    public function joinForm($code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        return view('player.join', compact('room'));
    }

    public function join(Request $request, $code)
    {
        $request->validate(['name' => 'required|string|max:20']);
        $room = Room::where('code', $code)->firstOrFail();

        $player = Player::updateOrCreate(
            ['session_id' => session()->getId(), 'room_id' => $room->id],
            ['name' => $request->name]
        );

        session(['player_id' => $player->id]);
        return redirect()->route('player.play', $code);
    }

    public function play($code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        $player = Player::findOrFail(session('player_id'));
        return view('player.buzzer', compact('room', 'player'));
    }

    public function buzz(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        $player = Player::findOrFail(session('player_id'));

        if ($room->status !== 'playing') {
            return response()->json(['error' => 'Game belum dimulai / dikunci'], 403);
        }

        // Fitur Atomic Lock: Mencegah bentrok jika ditekan bersamaan dalam hitungan milidetik
        $lock = Cache::lock("buzzer_lock_{$room->id}", 10);

        if ($lock->get()) {
            $room->update(['status' => 'locked']);
            $reactionTime = $request->reaction_time ?? 0;

            Buzz::create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'reaction_time_ms' => $reactionTime
            ]);

            broadcast(new PlayerBuzzed($player, $reactionTime));
            broadcast(new GameStateChanged($room->id, 'locked'));

            return response()->json(['status' => 'winner']);
        }

        return response()->json(['status' => 'late']);
    }
}

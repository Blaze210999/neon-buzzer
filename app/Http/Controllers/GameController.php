<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Events\GameStateChanged;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GameController extends Controller
{
    // Tampilan Layar Besar (Tanpa Tombol)
    public function display($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        $players = $room->players()->orderBy('score', 'desc')->get();
        $joinUrl = url("/join/{$code}");
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($joinUrl);

        return view('display.screen', compact('room', 'players', 'qrCode', 'joinUrl'));
    }

    // Tampilan Panel Kendali Rahasia (Banyak Tombol)
    public function admin($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        $players = $room->players()->orderBy('score', 'desc')->get();

        return view('admin.control', compact('room', 'players'));
    }

    public function control(Request $request, $code)
    {
        $room = Room::where('code', $code)->firstOrFail();
        $action = $request->action;

        if ($action === 'start') {
            $duration = $request->duration ?? 10;
            $endsAt = now()->addSeconds($duration);
            $room->update(['status' => 'playing', 'timer_ends_at' => $endsAt]);

            // Buka kunci buzzer
            cache()->forget("buzzer_lock_{$room->id}");

            broadcast(new GameStateChanged($room->id, 'start', $endsAt->toIso8601String()));
        } elseif ($action === 'reset') {
            $room->update(['status' => 'waiting', 'timer_ends_at' => null]);
            cache()->forget("buzzer_lock_{$room->id}");
            broadcast(new GameStateChanged($room->id, 'reset'));
        }

        return response()->json(['success' => true]);
    }
    // Update fungsi grade untuk mendukung multiplier
    public function grade(Request $request, $code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $player = $room->players()->findOrFail($request->player_id);

        // Ambil multiplier dari request, default-nya 1
        $multiplier = (int) ($request->multiplier ?? 1);

        if ($request->is_correct) {
            $player->increment('score', 100 * $multiplier);
        } else {
            $player->decrement('score', 50 * $multiplier);
            if ($player->score < 0) $player->update(['score' => 0]);
        }

        $room->update(['status' => 'waiting', 'timer_ends_at' => null]);
        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));

        return response()->json(['success' => true]);
    }

    // Tambahkan fungsi baru untuk reset semua skor
    public function resetScores($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $room->players()->update(['score' => 0]); // Setel semua skor ke 0

        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));

        return response()->json(['success' => true]);
    }
    public function timeout(Request $request, $code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();

        // Cari pemain yang tadi menekan buzzer
        $player = \App\Models\Player::find($request->player_id);

        if ($player) {
            // Kurangi skor 50 karena gagal menjawab (timeout)
            $player->decrement('score', 50);
            if ($player->score < 0) $player->update(['score' => 0]);
        }

        // Reset status ruangan
        $room->update(['status' => 'waiting', 'timer_ends_at' => null]);

        // Broadcast reset ke semua (Admin, Display, dan Players)
        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));

        return response()->json(['success' => true]);
    }
}

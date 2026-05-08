<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Events\GameStateChanged;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GameController extends Controller
{
    public function host($code)
    {
        $room = Room::firstOrCreate(['code' => $code]);
        $players = $room->players()->orderBy('score', 'desc')->get();
        $joinUrl = url("/join/{$code}");
        // Generate QR Code untuk discan HP
        $qrCode = QrCode::size(200)->generate($joinUrl);

        return view('host.dashboard', compact('room', 'players', 'qrCode', 'joinUrl'));
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
}

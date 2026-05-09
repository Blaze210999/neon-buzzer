<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Events\GameStateChanged;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GameController extends Controller
{
    public function display($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        $players = $room->players()->orderBy('score', 'desc')->get();

        // UBAH BARIS INI: Hapus tulisan /join/{$code} menjadi "/"
        $joinUrl = url("/");

        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($joinUrl);

        return view('display.screen', compact('room', 'players', 'qrCode', 'joinUrl'));
    }

    // Tampilan Dashboard Pilih Mode Game
    public function adminDashboard($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        return view('admin.dashboard', compact('room'));
    }

    // Tampilan Panel Kendali Rahasia (Banyak Tombol) - Berubah nama dari 'admin' ke 'adminControl'
    public function adminControl($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        $players = $room->players()->orderBy('score', 'desc')->get();

        return view('admin.control', compact('room', 'players'));
    }

    // Fungsi untuk mengakhiri permainan
    public function endGame($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();

        // Broadcast sinyal 'end_game' ke semua layar
        broadcast(new \App\Events\GameStateChanged($room->id, 'end_game'));

        return response()->json(['success' => true]);
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


    // Tambahkan fungsi baru untuk reset semua skor
    public function resetScores($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $room->players()->update(['score' => 0]); // Setel semua skor ke 0

        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));

        return response()->json(['success' => true]);
    }
    public function grade(Request $request, $code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $player = $room->players()->findOrFail($request->player_id);
        $multiplier = (int) ($request->multiplier ?? 1);

        $poinBerubah = 0;
        if ($request->is_correct) {
            $poinBerubah = $room->poin_benar * $multiplier;
            $player->increment('score', $poinBerubah);
        } else {
            $poinBerubah = - ($room->poin_salah * $multiplier);
            $player->decrement('score', abs($poinBerubah));
        }

        // CATAT KE AUDIT LOG
        \App\Models\GameLog::create([
            'room_id' => $room->id,
            'player_id' => $player->id,
            'action' => 'answer',
            'payload' => [
                'is_correct' => $request->is_correct,
                'multiplier' => $multiplier,
                'points_changed' => $poinBerubah,
                'new_total_score' => $player->fresh()->score
            ]
        ]);

        $room->update(['status' => 'waiting', 'timer_ends_at' => null]);
        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));
        return response()->json(['success' => true]);
    }

    public function timeout(Request $request, $code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $player = \App\Models\Player::find($request->player_id);

        if ($player) {
            $player->decrement('score', $room->poin_salah);

            // CATAT KE AUDIT LOG
            \App\Models\GameLog::create([
                'room_id' => $room->id,
                'player_id' => $player->id,
                'action' => 'penalty',
                'payload' => [
                    'reason' => 'waktu_habis',
                    'points_deducted' => $room->poin_salah,
                    'new_total_score' => $player->fresh()->score
                ]
            ]);
        }

        $room->update(['status' => 'waiting', 'timer_ends_at' => null]);
        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));
        return response()->json(['success' => true]);
    }
    public function kickPlayer($code, $playerId)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $player = \App\Models\Player::where('room_id', $room->id)->findOrFail($playerId);

        $player->delete(); // Hapus pemain dari database

        // Beri sinyal refresh ke semua layar agar nama pemain hilang dari klasemen
        broadcast(new \App\Events\GameStateChanged($room->id, 'reset'));

        return response()->json(['success' => true]);
    }

    public function lockLobby($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        cache(['room_locked_' . $room->id => true], now()->addHours(12)); // Kunci selama 12 jam
        broadcast(new \App\Events\GameStateChanged($room->id, 'lobby_locked'));
        return response()->json(['success' => true]);
    }

    public function unlockLobby($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        cache()->forget('room_locked_' . $room->id);
        broadcast(new \App\Events\GameStateChanged($room->id, 'lobby_unlocked'));
        return response()->json(['success' => true]);
    }
    // Halaman Pengaturan
    public function adminSettings($code)
    {
        $room = \App\Models\Room::firstOrCreate(['code' => $code]);
        return view('admin.settings', compact('room'));
    }

    // Simpan Pengaturan
    public function saveSettings(Request $request, $code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();
        $room->update([
            'timer_rebutan' => $request->timer_rebutan,
            'timer_menjawab' => $request->timer_menjawab,
            'poin_benar' => $request->poin_benar,
            'poin_salah' => $request->poin_salah,
        ]);
        return redirect()->route('game.admin.dashboard')->with('success', 'Pengaturan berhasil disimpan!');
    }
    // Halaman Audit Log (VAR)
    public function adminLogs($code)
    {
        $room = \App\Models\Room::where('code', $code)->firstOrFail();

        // Ambil data log, urutkan dari yang paling baru, dan sertakan relasi data pemainnya
        $logs = \App\Models\GameLog::with('player')
            ->where('room_id', $room->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.logs', compact('room', 'logs'));
    }
}

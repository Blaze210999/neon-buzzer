<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;

Route::post('/room/{code}/control', [GameController::class, 'control']);
Route::post('/room/{code}/buzz', [PlayerController::class, 'buzz']);
Route::post('/room/{code}/grade', [GameController::class, 'grade']);
Route::post('/room/{code}/reset-scores', [GameController::class, 'resetScores']);
Route::post('/room/{code}/timeout', [App\Http\Controllers\GameController::class, 'timeout']);
Route::delete('/room/{code}/player/{playerId}', [App\Http\Controllers\GameController::class, 'kickPlayer']);
Route::post('/room/{code}/end-game', [App\Http\Controllers\GameController::class, 'endGame']);
Route::post('/room/{code}/lock-lobby', [App\Http\Controllers\GameController::class, 'lockLobby']);
Route::post('/room/{code}/unlock-lobby', [App\Http\Controllers\GameController::class, 'unlockLobby']);
Route::get('/room/{code}/players', function ($code) {
    $room = \App\Models\Room::where('code', $code)->firstOrFail();
    return $room->players()->orderBy('score', 'desc')->get();
});
Route::get('/room/{code}/info', [App\Http\Controllers\GameController::class, 'roomInfo']);

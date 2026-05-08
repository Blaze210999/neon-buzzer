<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;

// Redirect awal ke layar utama
Route::get('/', function () {
    return redirect('/display/neon');
});

// Routes untuk Layar Utama (Proyektor) & Admin Panel
Route::get('/display/{code}', [GameController::class, 'display'])->name('game.display');
Route::get('/admin/{code}', [GameController::class, 'admin'])->name('game.admin');

// Routes untuk Player (HP)
Route::get('/join/{code}', [PlayerController::class, 'joinForm'])->name('player.joinForm');
Route::post('/join/{code}', [PlayerController::class, 'join'])->name('player.join');
Route::get('/play/{code}', [PlayerController::class, 'play'])->name('player.play');
require __DIR__ . '/auth.php';

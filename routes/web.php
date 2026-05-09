<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;

// 1. URL Utama (Root) langsung diarahkan ke HP Pemain (Buzzer)
Route::get('/', [PlayerController::class, 'joinForm'])->defaults('code', 'neon')->name('player.joinForm');
Route::post('/', [PlayerController::class, 'join'])->defaults('code', 'neon')->name('player.join');
Route::get('/play', [PlayerController::class, 'play'])->defaults('code', 'neon')->name('player.play');

// 2. URL untuk Layar Proyektor
Route::get('/display', [GameController::class, 'display'])->defaults('code', 'neon')->name('game.display');

// 3. URL untuk Admin Panel
Route::get('/admin', [GameController::class, 'adminDashboard'])->defaults('code', 'neon')->name('game.admin.dashboard');
Route::get('/admin/control', [GameController::class, 'adminControl'])->defaults('code', 'neon')->name('game.admin.control');

Route::get('/admin/settings', [GameController::class, 'adminSettings'])->defaults('code', 'neon')->name('game.admin.settings');
Route::post('/admin/settings', [GameController::class, 'saveSettings'])->defaults('code', 'neon')->name('game.admin.settings.save');
Route::get('/admin/logs', [GameController::class, 'adminLogs'])->defaults('code', 'neon')->name('game.admin.logs');

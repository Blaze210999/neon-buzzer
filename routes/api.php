<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;

Route::post('/room/{code}/control', [GameController::class, 'control']);
Route::post('/room/{code}/buzz', [PlayerController::class, 'buzz']);
Route::post('/room/{code}/grade', [GameController::class, 'grade']);
Route::post('/room/{code}/reset-scores', [GameController::class, 'resetScores']);
// Buka routes/api.php
Route::post('/room/{code}/timeout', [App\Http\Controllers\GameController::class, 'timeout']);

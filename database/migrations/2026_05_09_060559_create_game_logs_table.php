<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_logs', function (Blueprint $table) {
            $table->id();
            // Saya tambahkan room_id agar kamu tahu log ini dari sesi game yang mana
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // buzz, answer, reconnect, penalty
            $table->json('payload')->nullable(); // Simpan detail kejadian
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_logs');
    }
};

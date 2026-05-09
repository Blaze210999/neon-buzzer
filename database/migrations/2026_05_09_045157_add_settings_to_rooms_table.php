<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('timer_rebutan')->default(10);
            $table->integer('timer_menjawab')->default(30);
            $table->integer('poin_benar')->default(100);
            $table->integer('poin_salah')->default(50);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['timer_rebutan', 'timer_menjawab', 'poin_benar', 'poin_salah']);
        });
    }
};

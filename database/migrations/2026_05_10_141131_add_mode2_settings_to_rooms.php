<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('m2_timer_rebutan')->default(10);
            $table->integer('m2_timer_menjawab')->default(30);
            $table->string('m2_timer_start')->default('after'); // 'after' atau 'during'
            $table->string('active_mode')->default('mode1'); // Pelacak mode game saat ini
        });
    }
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['m2_timer_rebutan', 'm2_timer_menjawab', 'm2_timer_start', 'active_mode']);
        });
    }
};

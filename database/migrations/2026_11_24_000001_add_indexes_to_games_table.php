<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->index(['scheduled_time'], 'games_scheduled_time_index');
            $table->index(['status', 'scheduled_time'], 'games_status_scheduled_time_index');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_scheduled_time_index');
            $table->dropIndex('games_status_scheduled_time_index');
        });
    }
};

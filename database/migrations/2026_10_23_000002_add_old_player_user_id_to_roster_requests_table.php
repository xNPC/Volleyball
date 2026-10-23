<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_requests', function (Blueprint $table) {
            $table->foreignId('old_player_user_id')
                ->nullable()
                ->after('player_user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('roster_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('old_player_user_id');
        });
    }
};

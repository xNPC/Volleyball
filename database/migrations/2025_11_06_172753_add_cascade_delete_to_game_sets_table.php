<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Удаляем существующий внешний ключ
        Schema::table('game_sets', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
        });

        // Добавляем внешний ключ с каскадным удалением
        Schema::table('game_sets', function (Blueprint $table) {
            $table->foreign('game_id')
                ->references('id')
                ->on('games')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем каскадный внешний ключ
        Schema::table('game_sets', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
        });

        // Восстанавливаем оригинальный внешний ключ без каскада
        Schema::table('game_sets', function (Blueprint $table) {
            $table->foreign('game_id')
                ->references('id')
                ->on('games');
        });
    }
};

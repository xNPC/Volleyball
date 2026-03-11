<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playoff_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('tournament_stages')->onDelete('cascade');
            $table->string('format_type')->default('single_elimination');
            $table->integer('total_teams');
            $table->json('bracket_structure')->nullable();
            $table->json('rounds_config')->nullable();
            $table->json('tie_breakers')->nullable();
            $table->json('advancement_rules')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Уникальность: один этап - одна конфигурация
            $table->unique('stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_configs');
    }
};

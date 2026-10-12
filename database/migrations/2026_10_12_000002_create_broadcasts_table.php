<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->nullOnDelete();
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('vk_url')->nullable();
            $table->string('embed_url')->nullable();
            $table->text('embed_code')->nullable();
            $table->string('preview_image')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};

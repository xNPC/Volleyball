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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('tournament_stages');
            $table->foreignId('group_id')->nullable()->constrained('stage_groups');
            $table->foreignId('home_application_id')->constrained('tournament_applications');
            $table->foreignId('away_application_id')->constrained('tournament_applications');
            $table->foreignId('venue_id')->constrained();
            $table->dateTime('scheduled_time');
            $table->string('status')->default('scheduled');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->foreignId('first_referee_id')->nullable()->constrained('users');
            $table->foreignId('second_referee_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

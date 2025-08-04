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
        Schema::create('application_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('tournament_applications');
            $table->foreignId('user_id')->constrained();
            $table->integer('jersey_number')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_captain')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_rosters');
    }
};

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
        Schema::create('roster_requests', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_application_id')->nullable()->constrained('tournament_applications')->nullOnDelete();
            $table->foreignId('from_application_id')->nullable()->constrained('tournament_applications')->nullOnDelete();
            $table->string('jersey_number', 5)->nullable();
            $table->string('position', 50)->nullable();
            $table->text('comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['player_user_id', 'tournament_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_requests');
    }
};

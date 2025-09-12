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
        Schema::table('matches', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('stage_groups', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tournament_applications', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tournament_stages', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('venue_schedules', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

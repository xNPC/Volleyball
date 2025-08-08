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
        Schema::table('stage_groups', function (Blueprint $table) {
            $table->integer('order')->default(1)->change();
            $table->integer('team_count')->default(4);
            $table->boolean('is_active')->default(true);
            $table->json('configuration')->nullable();
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

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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->date('addition_deadline_male')->nullable();
            $table->date('addition_deadline_female')->nullable();
            $table->date('transfer_deadline_male')->nullable();
            $table->date('transfer_deadline_female')->nullable();
            $table->unsignedInteger('transfers_limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'addition_deadline_male',
                'addition_deadline_female',
                'transfer_deadline_male',
                'transfer_deadline_female',
                'transfers_limit',
            ]);
        });
    }
};

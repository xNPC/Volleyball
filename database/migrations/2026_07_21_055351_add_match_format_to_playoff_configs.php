<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('playoff_configs', 'match_format')) {
                $table->string('match_format')->default('single');
            }
        });
    }

    public function down(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            $table->dropColumn('match_format');
        });
    }
};

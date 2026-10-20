<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            if (Schema::hasColumn('playoff_configs', 'reverse_seeding')) {
                $table->dropColumn('reverse_seeding');
            }
        });
    }

    public function down(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('playoff_configs', 'reverse_seeding')) {
                $table->boolean('reverse_seeding')->default(false);
            }
        });
    }
};

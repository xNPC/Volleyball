<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            // Удаляем старые поля
            $columns = ['bracket_structure', 'rounds_config', 'seeding_rules', 'matchups'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('playoff_configs', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Добавляем новые упрощенные поля
            if (!Schema::hasColumn('playoff_configs', 'bye_positions')) {
                $table->json('bye_positions')->nullable();
            }
            if (!Schema::hasColumn('playoff_configs', 'reverse_seeding')) {
                $table->boolean('reverse_seeding')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('playoff_configs', function (Blueprint $table) {
            $table->dropColumn(['bye_positions', 'reverse_seeding']);
            $table->json('bracket_structure')->nullable();
            $table->json('rounds_config')->nullable();
            $table->json('seeding_rules')->nullable();
            $table->json('matchups')->nullable();
        });
    }
};

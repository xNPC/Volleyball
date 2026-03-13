<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Создаем временную новую таблицу
        Schema::create('playoff_configs_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('tournament_stages')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('stage_groups')->onDelete('cascade');
            $table->string('format_type')->default('single_elimination');
            $table->integer('total_teams');
            $table->json('bracket_structure')->nullable();
            $table->json('rounds_config')->nullable();
            $table->json('seeding_rules')->nullable();
            $table->json('matchups')->nullable();
            $table->timestamps();

            $table->unique(['stage_id', 'group_id']);
        });

        // Копируем данные из старой таблицы если она существует
        if (Schema::hasTable('playoff_configs')) {
            $oldConfigs = DB::table('playoff_configs')->get();

            foreach ($oldConfigs as $oldConfig) {
                DB::table('playoff_configs_new')->insert([
                    'stage_id' => $oldConfig->stage_id,
                    'group_id' => $oldConfig->group_id ?? null,
                    'format_type' => $oldConfig->format_type ?? 'single_elimination',
                    'total_teams' => $oldConfig->total_teams ?? 8,
                    'bracket_structure' => $oldConfig->bracket_structure,
                    'rounds_config' => $oldConfig->rounds_config,
                    'seeding_rules' => $oldConfig->seeding_rules ?? $oldConfig->seeding_rules ?? null,
                    'matchups' => $oldConfig->matchups,
                    'created_at' => $oldConfig->created_at ?? now(),
                    'updated_at' => $oldConfig->updated_at ?? now(),
                ]);
            }
        }

        // Переименовываем таблицы
        if (Schema::hasTable('playoff_configs')) {
            Schema::rename('playoff_configs', 'playoff_configs_old');
        }
        Schema::rename('playoff_configs_new', 'playoff_configs');
    }

    public function down(): void
    {
        Schema::dropIfExists('playoff_configs');

        if (Schema::hasTable('playoff_configs_old')) {
            Schema::rename('playoff_configs_old', 'playoff_configs');
        }
    }
};

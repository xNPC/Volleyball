<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $maleUsers = User::where('gender', 'male')
            ->where('email', '!=', 'admin@volleyball.local')
            ->limit(12)
            ->get();

        $femaleUsers = User::where('gender', 'female')
            ->where('email', '!=', 'admin@volleyball.local')
            ->limit(4)
            ->get();

        $maleTeamNames = [
            'Спартак', 'Динамо', 'Локомотив', 'Зенит',
            'ЦСКА', 'Трактор', 'Урал', 'Факел',
            'Газпром', 'Луч', 'Торпедо', 'Сокол',
        ];

        $femaleTeamNames = [
            'Калина', 'Берёзка', 'Лада', 'Уралочка',
        ];

        foreach ($maleTeamNames as $index => $name) {
            Team::updateOrCreate(
                ['name' => $name],
                [
                    'captain_id' => $maleUsers[$index]->id,
                    'description' => 'Мужская команда по волейболу',
                ]
            );
        }

        foreach ($femaleTeamNames as $index => $name) {
            Team::updateOrCreate(
                ['name' => $name],
                [
                    'captain_id' => $femaleUsers[$index]->id,
                    'description' => 'Женская команда по волейболу',
                ]
            );
        }
    }
}

<?php

namespace App\Services;

class PlayoffConfigurator
{
    /**
     * Создает конфигурацию для любого количества команд
     */
    public function createConfig(int $teamCount, array $settings = []): array
    {
        return [
            'format_type' => $settings['format_type'] ?? 'single_elimination',
            'total_teams' => $teamCount,
            'rounds_config' => $this->generateRoundsConfig($teamCount, $settings),
            'tie_breakers' => $settings['tie_breakers'] ?? [
                    'primary' => 'wins',
                    'secondary' => 'points',
                    'tertiary' => 'sets',
                ],
            'advancement_rules' => $settings['advancement_rules'] ?? [
                    'from_groups' => 'positions:1-2', // Берутся первые 2 места из каждой группы
                    'special_byes' => $settings['special_byes'] ?? [], // Кто сразу проходит дальше
                ],
            'metadata' => [
                'description' => $settings['description'] ?? '',
                'created_at' => now(),
            ],
        ];
    }

    /**
     * Генерирует конфигурацию раундов
     */
    private function generateRoundsConfig(int $teamCount, array $settings): array
    {
        $rounds = [];
        $roundNumber = 1;
        $currentTeams = $teamCount;

        // Учитываем специальные "байки" (команды, которые сразу проходят дальше)
        $specialByes = $settings['special_byes'] ?? [];
        $currentTeams += count($specialByes);

        while ($currentTeams > 1) {
            $matchesInRound = floor($currentTeams / 2);
            $teamsInNextRound = ceil($currentTeams / 2);

            $rounds[$roundNumber] = [
                'teams' => $currentTeams,
                'matches' => $matchesInRound,
                'type' => $this->determineMatchType($matchesInRound, $settings, $roundNumber),
                'best_of' => $settings['rounds'][$roundNumber]['best_of'] ?? 1,
                'tie_breaker' => $settings['rounds'][$roundNumber]['tie_breaker'] ?? 'golden_set',
                'games_per_match' => $settings['rounds'][$roundNumber]['games'] ?? 1,
                'advance_to_next' => $teamsInNextRound,
            ];

            $currentTeams = $teamsInNextRound;
            $roundNumber++;
        }

        return $rounds;
    }

    /**
     * Определяет тип матча
     */
    private function determineMatchType(int $matchesInRound, array $settings, int $roundNumber): string
    {
        // Проверяем, указан ли тип для конкретного раунда
        if (isset($settings['rounds'][$roundNumber]['type'])) {
            return $settings['rounds'][$roundNumber]['type'];
        }

        // Определяем по количеству матчей
        if ($matchesInRound === 1) {
            return $roundNumber === 1 ? 'final' : 'final';
        }

        if ($matchesInRound === 2) {
            return 'semifinal';
        }

        if ($matchesInRound === 4) {
            return 'quarterfinal';
        }

        return 'regular';
    }

    /**
     * Генерирует примеры конфигураций для разных сценариев
     */
    public function getPresets(): array
    {
        return [
            '8_teams_standard' => [
                'name' => '8 команд, стандартный плейофф',
                'config' => [
                    'format_type' => 'single_elimination',
                    'rounds_config' => [
                        1 => ['name' => '1/4 финала', 'best_of' => 1],
                        2 => ['name' => '1/2 финала', 'best_of' => 1],
                        3 => ['name' => 'Финал', 'best_of' => 3],
                    ],
                ],
            ],
            '8_teams_with_byes' => [
                'name' => '8 команд, 1 и 2 места сразу в полуфинал',
                'config' => [
                    'format_type' => 'custom',
                    'special_byes' => [
                        ['position' => 1, 'to_round' => 'semifinal'],
                        ['position' => 2, 'to_round' => 'semifinal'],
                    ],
                    'rounds_config' => [
                        1 => ['name' => '1/4 финала', 'teams' => 4, 'best_of' => 1],
                        2 => ['name' => '1/2 финала', 'teams' => 4, 'best_of' => 1],
                        3 => ['name' => 'Финал', 'teams' => 2, 'best_of' => 3],
                    ],
                ],
            ],
            '12_teams_complex' => [
                'name' => '12 команд, сложная схема',
                'config' => [
                    'format_type' => 'custom',
                    'special_byes' => [
                        ['position' => 1, 'to_round' => 'quarterfinal'],
                        ['position' => 2, 'to_round' => 'quarterfinal'],
                    ],
                    'rounds_config' => [
                        1 => ['name' => 'Предварительный раунд', 'teams' => 4, 'best_of' => 1],
                        2 => ['name' => '1/4 финала', 'teams' => 8, 'best_of' => 1],
                        3 => ['name' => '1/2 финала', 'teams' => 4, 'best_of' => 3],
                        4 => ['name' => 'Финал', 'teams' => 2, 'best_of' => 5],
                    ],
                ],
            ],
            '16_teams_series' => [
                'name' => '16 команд, серии до 2 побед',
                'config' => [
                    'format_type' => 'single_elimination',
                    'rounds_config' => [
                        1 => ['name' => '1/8 финала', 'best_of' => 1],
                        2 => ['name' => '1/4 финала', 'best_of' => 3],
                        3 => ['name' => '1/2 финала', 'best_of' => 3],
                        4 => ['name' => 'Финал', 'best_of' => 5],
                    ],
                ],
            ],
        ];
    }
}

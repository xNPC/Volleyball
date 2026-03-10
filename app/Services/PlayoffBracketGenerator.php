<?php

namespace App\Services;

use App\Models\TournamentStage;
use App\Models\StageGroup;
use Illuminate\Support\Collection;

class PlayoffBracketGenerator
{
    /**
     * Генерирует полную структуру плейофф
     */
    public function generateBracket(TournamentStage $stage): array
    {
        $config = $stage->playoff_config;
        $teams = $this->getQualifiedTeams($stage);

        // Определяем структуру на основе количества команд
        $structure = $this->determineBracketStructure(count($teams), $config);

        // Генерируем раунды
        $bracket = $this->generateRounds($structure, $teams, $config);

        return $bracket;
    }

    /**
     * Определяет оптимальную структуру сетки
     */
    private function determineBracketStructure(int $teamCount, array $config): array
    {
        $structure = [
            'rounds' => [],
            'total_rounds' => 0,
            'teams_per_round' => [],
        ];

        if ($config['format_type'] === 'single_elimination') {
            $rounds = ceil(log($teamCount, 2));
            $currentTeams = $teamCount;

            for ($i = 1; $i <= $rounds; $i++) {
                $roundName = $this->getRoundName($currentTeams, $i, $rounds);

                $structure['rounds'][] = [
                    'number' => $i,
                    'name' => $roundName,
                    'teams' => $currentTeams,
                    'matches' => floor($currentTeams / 2),
                    'type' => $config['rounds_config'][$i]['type'] ?? 'single_match',
                    'tie_breaker' => $config['rounds_config'][$i]['tie_breaker'] ?? 'golden_set',
                ];

                $currentTeams = floor($currentTeams / 2);
            }

            $structure['total_rounds'] = $rounds;
        }

        return $structure;
    }

    /**
     * Генерирует все раунды с матчами
     */
    private function generateRounds(array $structure, Collection $teams, array $config): array
    {
        $bracket = [];
        $seededTeams = $this->seedTeams($teams, $config);

        foreach ($structure['rounds'] as $round) {
            $roundData = [
                'round_number' => $round['number'],
                'round_name' => $round['name'],
                'matches' => [],
                'type' => $round['type'],
                'tie_breaker' => $round['tie_breaker'],
                'status' => 'pending',
            ];

            // Генерируем матчи для раунда
            for ($i = 1; $i <= $round['matches']; $i++) {
                $matchData = [
                    'match_number' => $i,
                    'home_team' => null,
                    'away_team' => null,
                    'home_score' => null,
                    'away_score' => null,
                    'home_wins' => 0,
                    'away_wins' => 0,
                    'home_points' => 0,
                    'away_points' => 0,
                    'home_sets_won' => 0,
                    'away_sets_won' => 0,
                    'status' => 'scheduled',
                    'games' => [],
                    'next_match' => $this->calculateNextMatch($round['number'], $i, $structure),
                    'winner_goes_to' => $this->getWinnerDestination($round['number'], $i, $structure),
                ];

                // Если это первый раунд, заполняем командами
                if ($round['number'] === 1) {
                    $teamIndex = ($i - 1) * 2;
                    if (isset($seededTeams[$teamIndex])) {
                        $matchData['home_team'] = $seededTeams[$teamIndex];
                    }
                    if (isset($seededTeams[$teamIndex + 1])) {
                        $matchData['away_team'] = $seededTeams[$teamIndex + 1];
                    }
                }

                $roundData['matches'][] = $matchData;
            }

            $bracket[] = $roundData;
        }

        return $bracket;
    }

    /**
     * Создает посев команд
     */
    private function seedTeams(Collection $teams, array $config): array
    {
        $teamsArray = $teams->values()->toArray();

        if ($config['format_type'] === 'single_elimination') {
            // Классический посев: 1-8, 4-5, 3-6, 2-7 для 8 команд
            $seeded = [];
            $count = count($teamsArray);

            for ($i = 0; $i < $count / 2; $i++) {
                $seeded[] = $teamsArray[$i];           // верхняя половина
                $seeded[] = $teamsArray[$count - 1 - $i]; // нижняя половина
            }

            return $seeded;
        }

        return $teamsArray;
    }

    /**
     * Получает команды, прошедшие в плейофф
     */
    private function getQualifiedTeams(TournamentStage $stage): Collection
    {
        $teams = collect();

        // Получаем команды из групп предыдущего этапа
        $previousStage = $stage->tournament->stages()
            ->where('order', '<', $stage->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousStage && $previousStage->stage_type === 'group') {
            $previousStage->load('groups.teams.team');

            foreach ($previousStage->groups as $group) {
                // Сортируем команды по позиции в группе
                $qualified = $group->teams->sortBy('pivot.position');
                $teams = $teams->concat($qualified);
            }
        }

        return $teams;
    }

    /**
     * Получает название раунда
     */
    private function getRoundName(int $teams, int $roundNumber, int $totalRounds): string
    {
        if ($roundNumber === $totalRounds) {
            return 'Финал';
        }

        if ($roundNumber === $totalRounds - 1) {
            return '1/2 финала';
        }

        if ($roundNumber === $totalRounds - 2) {
            return '1/4 финала';
        }

        return "1/{$teams} финала";
    }

    /**
     * Вычисляет следующий матч для победителя
     */
    private function calculateNextMatch(int $round, int $match, array $structure): array
    {
        if ($round === $structure['total_rounds']) {
            return ['type' => 'champion'];
        }

        $nextMatchNumber = ceil($match / 2);
        $nextMatchPosition = ($match % 2 === 1) ? 'home' : 'away';

        return [
            'round' => $round + 1,
            'match' => $nextMatchNumber,
            'position' => $nextMatchPosition,
        ];
    }

    /**
     * Определяет, куда идет победитель
     */
    private function getWinnerDestination(int $round, int $match, array $structure): string
    {
        if ($round === $structure['total_rounds']) {
            return 'champion';
        }

        $nextMatchNumber = ceil($match / 2);
        return "r" . ($round + 1) . "m" . $nextMatchNumber;
    }
}

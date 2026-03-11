<?php

namespace App\Services;

use App\Models\TournamentStage;
use App\Models\StageGroup;
use App\Models\Game;
use Illuminate\Support\Collection;

class PlayoffBracketGenerator
{
    /**
     * Генерирует полную структуру плейофф
     */
    public function generateBracket(TournamentStage $stage, Collection $teams = null, array $groupConfig = null): array
    {
        $config = $stage->playoffConfig ? $stage->playoffConfig->toArray() : [];

        if ($groupConfig) {
            $config = array_merge($config, $groupConfig);
        }

        if (!$teams || $teams->isEmpty()) {
            return [];
        }

        // Сортируем команды по позиции (посеву)
        $seededTeams = $teams->sortBy('pivot.position')->values();

        // Получаем ID группы из первой команды
        $groupId = $teams->first()->pivot->group_id ?? null;

        // Получаем игры ТОЛЬКО для этой группы
        $games = collect();
        if ($groupId) {
            $games = Game::where('group_id', $groupId)
                ->with(['sets', 'homeApplication.team', 'awayApplication.team'])
                ->get();
        }

        // Используем конфигурацию из playoffConfig для определения структуры
        $structure = $this->buildStructureFromConfig(count($seededTeams), $config);

        // Генерируем сетку с определением победителей
        $bracket = $this->generateBracketWithWinners($structure, $seededTeams, $games, $groupId, $config);

        return $bracket;
    }

    /**
     * Генерирует сетку с определением победителей
     */
    private function generateBracketWithWinners(array $structure, Collection $seededTeams, Collection $games, ?int $groupId, array $config): array
    {
        $bracket = [];
        $winners = []; // Хранилище победителей по матчам

        foreach ($structure['rounds'] as $roundIndex => $round) {
            $roundNumber = $round['number'] ?? $roundIndex + 1;
            $roundData = [
                'round_number' => $roundNumber,
                'round_name' => $round['name'] ?? "Раунд {$roundNumber}",
                'matches' => [],
                'status' => 'pending',
            ];

            // Определяем количество матчей в раунде
            $matchesCount = $round['matches'] ?? count($round['matchups'] ?? []);

            for ($matchIndex = 0; $matchIndex < $matchesCount; $matchIndex++) {
                $matchNumber = $matchIndex + 1;

                // Получаем конфигурацию матча из структуры
                $matchConfig = $round['matchups'][$matchIndex] ?? [];

                // Определяем команды для этого матча
                $homeTeam = null;
                $awayTeam = null;

                if ($roundNumber === 1) {
                    // Первый раунд - берем команды по позициям из конфига
                    $homePosition = $matchConfig['home_position'] ?? $matchConfig['home'] ?? ($matchIndex * 2 + 1);
                    $awayPosition = $matchConfig['away_position'] ?? $matchConfig['away'] ?? ($matchIndex * 2 + 2);

                    // Преобразуем "position:1" в число
                    if (is_string($homePosition) && strpos($homePosition, 'position:') === 0) {
                        $homePosition = (int) str_replace('position:', '', $homePosition);
                    }
                    if (is_string($awayPosition) && strpos($awayPosition, 'position:') === 0) {
                        $awayPosition = (int) str_replace('position:', '', $awayPosition);
                    }

                    $homeTeam = $seededTeams->firstWhere('pivot.position', $homePosition);
                    $awayTeam = $seededTeams->firstWhere('pivot.position', $awayPosition);
                } else {
                    // Последующие раунды - берем победителей из предыдущих матчей
                    $homeFromMatch = $matchConfig['home_from'] ?? ($matchIndex * 2 + 1);
                    $awayFromMatch = $matchConfig['away_from'] ?? ($matchIndex * 2 + 2);

                    // Получаем победителей из предыдущего раунда
                    $homeWinner = $winners[$roundNumber - 1][$homeFromMatch] ?? null;
                    $awayWinner = $winners[$roundNumber - 1][$awayFromMatch] ?? null;

                    if ($homeWinner) {
                        $homeTeam = $homeWinner['team'];
                    }
                    if ($awayWinner) {
                        $awayTeam = $awayWinner['team'];
                    }
                }

                // Ищем игру между этими командами
                $game = $this->findGameInGroup($games, $homeTeam, $awayTeam, $groupId);

                // Определяем победителя матча
                $winner = $this->determineMatchWinner($game, $homeTeam, $awayTeam);

                // Сохраняем победителя для следующих раундов
                if (!isset($winners[$roundNumber])) {
                    $winners[$roundNumber] = [];
                }

                if ($winner) {
                    $winners[$roundNumber][$matchNumber] = [
                        'team' => $winner,
                        'from_match' => $matchNumber,
                        'round' => $roundNumber,
                    ];
                }

                $matchData = [
                    'match_number' => $matchNumber,
                    'match_config' => $matchConfig,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'home_position' => $homeTeam->pivot->position ?? null,
                    'away_position' => $awayTeam->pivot->position ?? null,
                    'home_score' => $game->home_score ?? null,
                    'away_score' => $game->away_score ?? null,
                    'home_wins' => 0,
                    'away_wins' => 0,
                    'status' => $game->status ?? 'scheduled',
                    'game_id' => $game->id ?? null,
                    'sets' => [],
                    'winner' => $winner ? ($winner->id == $homeTeam?->id ? 'home' : 'away') : null,
                    'winner_team' => $winner,
                    'next_match' => $this->getNextMatchFromConfig($roundNumber, $matchNumber, $structure, $config),
                ];

                // Если есть игра, добавляем детали
                if ($game) {
                    $matchData['home_wins'] = $game->home_score > $game->away_score ? 1 : 0;
                    $matchData['away_wins'] = $game->away_score > $game->home_score ? 1 : 0;

                    foreach ($game->sets as $set) {
                        $matchData['sets'][] = [
                            'set_number' => $set->set_number,
                            'home_score' => $set->home_score,
                            'away_score' => $set->away_score,
                        ];
                    }
                }

                $roundData['matches'][] = $matchData;
            }

            $bracket[] = $roundData;
        }

        return $bracket;
    }

    /**
     * Определяет победителя матча
     */
    private function determineMatchWinner(?Game $game, $homeTeam, $awayTeam)
    {
        if (!$game || !$homeTeam || !$awayTeam) {
            return null;
        }

        if ($game->home_score > $game->away_score) {
            return $homeTeam;
        } elseif ($game->away_score > $game->home_score) {
            return $awayTeam;
        }

        return null;
    }

    /**
     * Ищет игру между командами в конкретной группе
     */
    private function findGameInGroup(Collection $games, $homeTeam, $awayTeam, ?int $groupId): ?Game
    {
        if (!$homeTeam || !$awayTeam || !$groupId) {
            return null;
        }

        return $games->first(function ($game) use ($homeTeam, $awayTeam, $groupId) {
            // Проверяем, что игра принадлежит нужной группе
            if ($game->group_id != $groupId) {
                return false;
            }

            // Проверяем, что игра между этими командами
            return ($game->home_application_id == $homeTeam->id && $game->away_application_id == $awayTeam->id) ||
                ($game->home_application_id == $awayTeam->id && $game->away_application_id == $homeTeam->id);
        });
    }

    /**
     * Строит структуру на основе конфигурации
     */
    private function buildStructureFromConfig(int $teamCount, array $config): array
    {
        if (!empty($config['bracket_structure'])) {
            return [
                'rounds' => $config['bracket_structure'],
                'total_rounds' => count($config['bracket_structure']),
                'matchups' => $config['matchups'] ?? [],
            ];
        }

        return $this->determineBracketStructure($teamCount, $config);
    }

    /**
     * Определяет оптимальную структуру сетки (стандартная)
     */
    private function determineBracketStructure(int $teamCount, array $config = []): array
    {
        $structure = [
            'rounds' => [],
            'total_rounds' => 0,
            'matchups' => [],
        ];

        $formatType = $config['format_type'] ?? 'single_elimination';

        if ($formatType === 'single_elimination') {
            $rounds = ceil(log($teamCount, 2));
            $currentTeams = $teamCount;

            // Создаем схему распределения команд
            $matchups = $this->createSeedingMatchups($teamCount);

            for ($i = 1; $i <= $rounds; $i++) {
                $roundName = $this->getRoundName($currentTeams, $i, $rounds);

                $structure['rounds'][] = [
                    'number' => $i,
                    'name' => $roundName,
                    'teams' => $currentTeams,
                    'matches' => floor($currentTeams / 2),
                    'matchups' => $matchups[$i] ?? [],
                ];

                $currentTeams = floor($currentTeams / 2);
            }

            $structure['total_rounds'] = $rounds;
            $structure['matchups'] = $matchups;
        }

        return $structure;
    }

    /**
     * Создает стандартную схему распределения команд
     */
    private function createSeedingMatchups(int $teamCount): array
    {
        $matchups = [];

        // Создаем матчи первого раунда
        $firstRoundMatches = [];
        for ($i = 0; $i < $teamCount / 2; $i++) {
            $firstRoundMatches[] = [
                'home_position' => $i + 1,
                'away_position' => $teamCount - $i,
            ];
        }
        $matchups[1] = $firstRoundMatches;

        // Создаем последующие раунды
        $currentRound = 1;
        $matchesCount = count($firstRoundMatches);

        while ($matchesCount > 1) {
            $nextRound = [];
            for ($i = 0; $i < $matchesCount / 2; $i++) {
                $nextRound[] = [
                    'home_from' => $i * 2 + 1,
                    'away_from' => $i * 2 + 2,
                ];
            }
            $matchups[$currentRound + 1] = $nextRound;
            $matchesCount = count($nextRound);
            $currentRound++;
        }

        return $matchups;
    }

    /**
     * Получает информацию о следующем матче из конфигурации
     */
    private function getNextMatchFromConfig(int $round, int $match, array $structure, array $config): ?array
    {
        if (!empty($config['next_matches']) && isset($config['next_matches'][$round][$match])) {
            return $config['next_matches'][$round][$match];
        }

        if ($round === $structure['total_rounds']) {
            return ['type' => 'champion'];
        }

        $nextRound = $round + 1;
        $nextMatch = ceil($match / 2);
        $position = ($match % 2 === 1) ? 'home' : 'away';

        return [
            'round' => $nextRound,
            'match' => $nextMatch,
            'position' => $position,
        ];
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
}

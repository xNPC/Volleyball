<?php

namespace App\Services;

use App\Models\TournamentStage;
use App\Models\StageGroup;
use App\Models\Game;
use App\Models\PlayoffConfig;
use Illuminate\Support\Collection;

class PlayoffBracketGenerator
{
    /**
     * Генерирует полную структуру плейофф
     */
    public function generateBracket(TournamentStage $stage, Collection $teams = null, $groupId = null): array
    {
        \Log::info('PlayoffBracketGenerator::generateBracket', [
            'stage_id' => $stage->id,
            'teams_count' => $teams ? $teams->count() : 0,
            'group_id' => $groupId
        ]);

        if (!$teams || $teams->isEmpty()) {
            \Log::warning('No teams provided to bracket generator');
            return [];
        }

        // Логируем первую команду для проверки
        $firstTeam = $teams->first();
        \Log::info('First team data', [
            'id' => $firstTeam->id,
            'name' => $firstTeam->name,
            'position' => $firstTeam->position ?? null
        ]);

        $config = $stage->playoffConfig ? $stage->playoffConfig->toArray() : [];

        // Сортируем команды по позиции (посеву)
        $seededTeams = $teams->sortBy('position')->values();

        // Получаем игры для этой группы
        $games = collect();
        if ($groupId) {
            $games = Game::where('group_id', $groupId)
                ->with(['sets', 'homeApplication.team', 'awayApplication.team'])
                ->get();
            \Log::info('Games found', ['count' => $games->count()]);
        }

        // Получаем настройки из конфига
        $seedingRules = $config['seeding_rules'] ?? [];
        $specialByes = $seedingRules['special_byes'] ?? [];
        $reverseSeeding = $seedingRules['reverse'] ?? false;
        $matchups = $config['matchups'] ?? [];
        $roundsConfig = $config['rounds_config'] ?? [];

        // Генерируем сетку
        $bracket = $this->generateCustomBracket(
            $seededTeams,
            $games,
            $groupId,
            $specialByes,
            $reverseSeeding,
            $matchups,
            $roundsConfig
        );

        return $bracket;
    }

    /**
     * Генерирует кастомную сетку с учетом BYE
     */
    private function generateCustomBracket(
        Collection $seededTeams,
        Collection $games,
        ?int $groupId,
        array $specialByes,
        bool $reverseSeeding,
        array $matchups,
        array $roundsConfig
    ): array {
        $bracket = [];
        $winners = []; // Хранилище победителей по раундам и матчам

        // ВРЕМЕННОЕ! УБРАТЬ!!!

        // Определяем раунды из matchups
        $rounds = array_keys($matchups);
        if (empty($rounds)) {
            return $this->generateStandardBracket($seededTeams, $games, $groupId, $specialByes, $reverseSeeding);
        }

        // Проходим по каждому раунду
        foreach ($rounds as $roundNumber) {
            $roundMatches = $matchups[$roundNumber] ?? [];

            // Получаем название раунда из конфига
            $roundName = $roundsConfig[$roundNumber]['name'] ?? $this->getRoundNameByMatchCount(count($roundMatches));

            $roundData = [
                'round_number' => (int)$roundNumber,
                'round_name' => $roundName,
                'matches' => [],
                'status' => 'pending',
            ];

            // Обрабатываем каждый матч в раунде
            foreach ($roundMatches as $matchIndex => $matchConfig) {
                $matchNumber = $matchIndex + 1;

                // Определяем команды для этого матча
                $homeTeam = null;
                $awayTeam = null;
                $homePos = null;
                $awayPos = null;

                if ($roundNumber == 1) {
                    $homePos = $matchConfig['home'] ?? $matchConfig['home_position'] ?? null;
                    $awayPos = $matchConfig['away'] ?? $matchConfig['away_position'] ?? null;

                    // Находим команды
                    $homeTeam = null;
                    $awayTeam = null;

                    foreach ($seededTeams as $team) {
                        if ($team->position == $homePos) {
                            $homeTeam = $team;
                        }
                        if ($team->position == $awayPos) {
                            $awayTeam = $team;
                        }
                    }

                    // Проверяем BYE
                    $homeBye = $this->findByeForPosition($homePos, $specialByes);
                    $awayBye = $this->findByeForPosition($awayPos, $specialByes);

                    // Если есть BYE, команда не участвует в матче, но должна быть в следующем раунде
                    if ($homeBye) {
                        $winners[$homeBye['round']][$matchNumber] = [
                            'team' => $homeTeam,
                            'from_match' => $matchNumber,
                            'round' => $homeBye['round'],
                        ];
                        $homeTeam = null; // Не показываем в этом матче
                    }

                    if ($awayBye) {
                        $winners[$awayBye['round']][$matchNumber] = [
                            'team' => $awayTeam,
                            'from_match' => $matchNumber,
                            'round' => $awayBye['round'],
                        ];
                        $awayTeam = null; // Не показываем в этом матче
                    }
                } else {
                    // Последующие раунды - берем победителей из предыдущего раунда
                    $homeFrom = $matchConfig['home_from'] ?? null;
                    $awayFrom = $matchConfig['away_from'] ?? null;

                    if ($homeFrom && isset($winners[$roundNumber - 1][$homeFrom])) {
                        $homeTeam = $winners[$roundNumber - 1][$homeFrom]['team'];
                        $homePos = $homeTeam->position ?? null;
                    }

                    if ($awayFrom && isset($winners[$roundNumber - 1][$awayFrom])) {
                        $awayTeam = $winners[$roundNumber - 1][$awayFrom]['team'];
                        $awayPos = $awayTeam->position ?? null;
                    }
                }

                // Ищем игру между этими командами
                $game = $this->findGameInGroup($games, $homeTeam, $awayTeam, $groupId);

                // Определяем победителя матча
                $winner = null;
                if ($game && $homeTeam && $awayTeam) {
                    if ($game->home_score > $game->away_score) {
                        $winner = $homeTeam;
                    } elseif ($game->away_score > $game->home_score) {
                        $winner = $awayTeam;
                    }
                }

                // Сохраняем победителя для следующих раундов
                if ($winner) {
                    if (!isset($winners[$roundNumber])) {
                        $winners[$roundNumber] = [];
                    }
                    $winners[$roundNumber][$matchNumber] = [
                        'team' => $winner,
                        'from_match' => $matchNumber,
                        'round' => $roundNumber,
                    ];
                }

                // Формируем данные матча
                $matchData = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'home_position' => $homePos,
                    'away_position' => $awayPos,
                    'home_score' => $game->home_score ?? null,
                    'away_score' => $game->away_score ?? null,
                    'status' => $game->status ?? 'scheduled',
                    'game' => $game,
                    'sets' => [],
                    'winner' => $winner ? ($winner->id == $homeTeam?->id ? 'home' : 'away') : null,
                    'next_match' => $this->findNextMatch($roundNumber, $matchNumber, $matchups),
                ];

                // Добавляем сеты если есть
                if ($game) {
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
     * Генерирует стандартную сетку (для случаев без конфига)
     */
    private function generateStandardBracket(
        Collection $seededTeams,
        Collection $games,
        ?int $groupId,
        array $specialByes,
        bool $reverseSeeding
    ): array {
        $teamCount = $seededTeams->count();
        $rounds = ceil(log($teamCount, 2));
        $bracket = [];
        $winners = [];

        // Создаем первый раунд
        $firstRoundMatches = [];
        for ($i = 0; $i < $teamCount / 2; $i++) {
            $homePos = $i + 1;
            $awayPos = $teamCount - $i;

            $homeBye = $this->findByeForPosition($homePos, $specialByes);
            $awayBye = $this->findByeForPosition($awayPos, $specialByes);

            $homeTeam = !$homeBye ? $seededTeams->firstWhere('pivot.position', $homePos) : null;
            $awayTeam = !$awayBye ? $seededTeams->firstWhere('pivot.position', $awayPos) : null;

            $firstRoundMatches[] = [
                'home' => $homePos,
                'away' => $awayPos,
                'home_team' => $homeTeam,
                'away_team' => $awayTeam,
                'home_bye' => $homeBye,
                'away_bye' => $awayBye,
            ];
        }

        if ($reverseSeeding) {
            $firstRoundMatches = array_reverse($firstRoundMatches);
        }

        // Обрабатываем первый раунд
        $round1Matches = [];
        foreach ($firstRoundMatches as $matchIndex => $match) {
            $matchNumber = $matchIndex + 1;

            $game = $this->findGameInGroup($games, $match['home_team'], $match['away_team'], $groupId);
            $winner = $this->determineMatchWinner($game, $match['home_team'], $match['away_team']);

            if ($winner) {
                $winners[1][$matchNumber] = ['team' => $winner];
            }

            $round1Matches[] = [
                'match_number' => $matchNumber,
                'home_team' => $match['home_team'],
                'away_team' => $match['away_team'],
                'home_position' => $match['home'],
                'away_position' => $match['away'],
                'home_score' => $game->home_score ?? null,
                'away_score' => $game->away_score ?? null,
                'status' => $game->status ?? 'scheduled',
                'game' => $game,
                'sets' => $game ? $game->sets : [],
                'winner' => $winner ? ($winner->id == $match['home_team']?->id ? 'home' : 'away') : null,
            ];
        }

        $bracket[] = [
            'round_number' => 1,
            'round_name' => $this->getRoundName($teamCount),
            'matches' => $round1Matches,
        ];

        // Генерируем последующие раунды
        $currentTeams = $teamCount / 2;
        $currentRound = 2;

        while ($currentTeams >= 2) {
            $roundMatches = [];
            $matchesInRound = $currentTeams / 2;

            for ($i = 0; $i < $matchesInRound; $i++) {
                $matchNumber = $i + 1;
                $homeFrom = $i * 2 + 1;
                $awayFrom = $i * 2 + 2;

                $homeTeam = $winners[$currentRound - 1][$homeFrom]['team'] ?? null;
                $awayTeam = $winners[$currentRound - 1][$awayFrom]['team'] ?? null;

                $game = $this->findGameInGroup($games, $homeTeam, $awayTeam, $groupId);
                $winner = $this->determineMatchWinner($game, $homeTeam, $awayTeam);

                if ($winner) {
                    $winners[$currentRound][$matchNumber] = ['team' => $winner];
                }

                $roundMatches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'home_score' => $game->home_score ?? null,
                    'away_score' => $game->away_score ?? null,
                    'status' => $game->status ?? 'scheduled',
                    'game' => $game,
                    'sets' => $game ? $game->sets : [],
                    'winner' => $winner ? ($winner->id == $homeTeam?->id ? 'home' : 'away') : null,
                ];
            }

            $bracket[] = [
                'round_number' => $currentRound,
                'round_name' => $this->getRoundName($currentTeams),
                'matches' => $roundMatches,
            ];

            $currentTeams = $currentTeams / 2;
            $currentRound++;
        }

        return $bracket;
    }

    /**
     * Находит BYE для позиции
     */
    private function findByeForPosition(?int $position, array $specialByes): ?array
    {
        if (!$position) return null;

        foreach ($specialByes as $bye) {
            // Убеждаемся, что сравниваем числа
            if (isset($bye['position']) && (int)$bye['position'] == $position) {
                return $bye;
            }
        }
        return null;
    }

    /**
     * Находит следующий матч для победителя
     */
    private function findNextMatch(int $round, int $match, array $matchups): ?array
    {
        $nextRound = $round + 1;

        // Проверяем, есть ли следующий раунд
        if (!isset($matchups[$nextRound])) {
            return ['type' => 'champion'];
        }

        // Вычисляем номер следующего матча (в плейофф обычно победители матчей 1-2 идут в матч 1 следующего раунда)
        $nextMatchNumber = ceil($match / 2);
        $position = ($match % 2 == 1) ? 'home' : 'away';

        return [
            'round' => $nextRound,
            'match' => $nextMatchNumber,
            'position' => $position,
        ];
    }

    /**
     * Ищет игру между командами
     */
    private function findGameInGroup(Collection $games, $homeTeam, $awayTeam, ?int $groupId): ?Game
    {
        if (!$homeTeam || !$awayTeam || !$groupId) {
            return null;
        }

        return $games->first(function ($game) use ($homeTeam, $awayTeam, $groupId) {
            if ($game->group_id != $groupId) return false;

            return ($game->home_application_id == $homeTeam->id && $game->away_application_id == $awayTeam->id) ||
                ($game->home_application_id == $awayTeam->id && $game->away_application_id == $homeTeam->id);
        });
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
     * Получает название раунда по количеству команд
     */
    private function getRoundName(int $teams): string
    {
        $names = [
            2 => 'Финал',
            4 => '1/2 финала',
            8 => '1/4 финала',
            16 => '1/8 финала',
            32 => '1/16 финала',
        ];

        return $names[$teams] ?? "1/{$teams} финала";
    }

    /**
     * Получает название раунда по количеству матчей
     */
    private function getRoundNameByMatchCount(int $matches): string
    {
        $teams = $matches * 2;
        return $this->getRoundName($teams);
    }
}

<?php

namespace App\Services;

use App\Models\StageGroup;
use App\Models\Game;
use Illuminate\Support\Collection;

class PlayoffBracketService
{
    /**
     * Генерирует сетку плейофф для группы
     */
    public function generateBracket(StageGroup $group, array $config): array
    {
        $teams = $this->getTeamsWithPositions($group);
        $games = $this->getGames($group);

        $byePositions = $config['bye_positions'] ?? [];
        $matchFormat = $config['match_format'] ?? 'single';

        // ОТЛАДКА
        //\Log::info('=== BRACKET GENERATE START ===');
        //\Log::info('Group ID: ' . $group->id);
        //\Log::info('Teams count: ' . $teams->count());
        //\Log::info('Bye positions: ' . json_encode($byePositions));
        //\Log::info('Match format: ' . $matchFormat);

        // Получаем все позиции команд
        $allPositions = $teams->pluck('position')->sort()->values()->toArray();
        $byePositions = array_values(array_intersect($allPositions, $byePositions));
        $teamCount = count($allPositions);

        // Сетка дополняется до ближайшей степени двойки. BYE отдаются высшим посевам.
        $slots = $this->nextPowerOfTwo($teamCount);
        $requiredByes = $slots - $teamCount;
        $byePositions = array_values(array_unique($byePositions));

        $nonByeSeeds = array_values(array_diff($allPositions, $byePositions));
        while (count($byePositions) < $requiredByes && !empty($nonByeSeeds)) {
            $byePositions[] = array_shift($nonByeSeeds);
        }
        sort($byePositions);

        // Раскладка посевов по слотам сетки (олимпийская схема).
        $seedOrder = $this->buildSeedOrder($slots);
        $slotTeams = [];
        foreach ($seedOrder as $seed) {
            if ($seed > $teamCount) {
                $slotTeams[] = null;
            } else {
                $slotTeams[] = $teams->firstWhere('position', $seed);
            }
        }

        // Строим структуру
        $bracket = [];
        $winners = [];

        // === РАУНД 1 ===
        $round1Matches = [];
        $round1Slots = [];

        if ($slots >= 2) {
            for ($i = 0; $i < intdiv($slots, 2); $i++) {
                $matchNumber = $i + 1;
                $homeTeam = $slotTeams[$i * 2] ?? null;
                $awayTeam = $slotTeams[$i * 2 + 1] ?? null;

                if (!$homeTeam || !$awayTeam) {
                    $winner = $homeTeam ?: $awayTeam;
                    if ($winner) {
                        $winners[1][$matchNumber] = $winner;
                    }

                    $round1Matches[] = [
                        'match_number' => $matchNumber,
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'games' => [],
                        'home_wins' => 0,
                        'away_wins' => 0,
                        'winner' => null,
                        'status' => 'pending',
                        'match_format' => $matchFormat,
                        'is_bye' => ($homeTeam xor $awayTeam),
                    ];
                    $round1Slots[] = $winner;
                    continue;
                }

                $gamesBetween = $this->findAllGames($games, $homeTeam, $awayTeam);
                $seriesResult = $this->processSeries($gamesBetween, $homeTeam, $awayTeam, $matchFormat);

                $winner = $seriesResult['winner'] ?? null;
                if ($winner) {
                    $winners[1][$matchNumber] = $winner;
                }

                $round1Matches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'games' => $seriesResult['games'],
                    'home_wins' => $seriesResult['home_wins'],
                    'away_wins' => $seriesResult['away_wins'],
                    'winner' => $winner ? ($winner['id'] == $homeTeam['id'] ? 'home' : 'away') : null,
                    'status' => $seriesResult['status'],
                    'match_format' => $matchFormat,
                    'is_bye' => false,
                ];
                $round1Slots[] = $winner;
            }

            $bracket[] = [
                'round_number' => 1,
                'round_name' => $this->getRoundName(count($round1Matches)),
                'matches' => $round1Matches,
                'match_format' => $matchFormat,
            ];
        }

        // === ПРОМЕЖУТОЧНЫЕ РАУНДЫ (полуфиналы и т.д.) ===
        // Раунд 1 — всегда сыгранный раунд с парами. Далее собираем "сетку"
        // из победителей (и прошедших BYE) до тех пор, пока не останется финалистов.
        $lastPlayedRound = 1;
        $lastPlayedMatches = $round1Matches;
        $entryRound = 2;

        $participants = [];
        foreach ($round1Slots as $team) {
            $participants[] = ['team' => $team, 'type' => $team ? 'winner' : 'tbd'];
        }

        $currentFieldSize = count($participants);

        // Пока в сетке больше двух участников — проводим раунд парами.
        while ($currentFieldSize > 2) {
            $pairs = $this->pairParticipants($participants, $currentFieldSize);
            $roundMatches = [];
            $roundSlots = [];

            foreach ($pairs as $index => $pair) {
                $matchNumber = $index + 1;
                $homeTeam = $pair['home']['team'] ?? null;
                $awayTeam = $pair['away']['team'] ?? null;

                if (!$homeTeam || !$awayTeam) {
                    $winner = $homeTeam ?: $awayTeam;
                    if ($winner) {
                        $winners[$entryRound][$matchNumber] = $winner;
                    }

                    $roundMatches[] = [
                        'match_number' => $matchNumber,
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'games' => [],
                        'home_wins' => 0,
                        'away_wins' => 0,
                        'winner' => null,
                        'status' => 'pending',
                        'match_format' => $matchFormat,
                        'is_bye' => ($homeTeam xor $awayTeam),
                    ];
                    $roundSlots[] = $winner;
                    continue;
                }

                $gamesBetween = $this->findAllGames($games, $homeTeam, $awayTeam);
                $seriesResult = $this->processSeries($gamesBetween, $homeTeam, $awayTeam, $matchFormat);

                $winner = $seriesResult['winner'] ?? null;
                if ($winner) {
                    $winners[$entryRound][$matchNumber] = $winner;
                }

                $roundMatches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'games' => $seriesResult['games'],
                    'home_wins' => $seriesResult['home_wins'],
                    'away_wins' => $seriesResult['away_wins'],
                    'winner' => $winner ? ($winner['id'] == $homeTeam['id'] ? 'home' : 'away') : null,
                    'status' => $seriesResult['status'],
                    'match_format' => $matchFormat,
                    'is_bye' => false,
                ];
                $roundSlots[] = $winner;
            }

            $bracket[] = [
                'round_number' => $entryRound,
                'round_name' => $this->getRoundName(count($roundMatches)),
                'matches' => $roundMatches,
                'match_format' => $matchFormat,
            ];

            $lastPlayedRound = $entryRound;
            $lastPlayedMatches = $roundMatches;

            $participants = [];
            foreach ($roundSlots as $team) {
                $participants[] = ['team' => $team, 'type' => $team ? 'winner' : 'tbd'];
            }
            $currentFieldSize = count($participants);
            $entryRound++;
        }

        // Переименовываем сыгранные раунды по их позиции относительно финала:
        // последний играющий раунд — полуфинал, предыдущий — 1/4 и т.д.
        $playedCount = count($bracket);
        foreach ($bracket as $i => $round) {
            $roundsLeftToFinal = $playedCount - $i;
            $bracket[$i]['round_name'] = $this->getRoundNameByDistance($roundsLeftToFinal);
        }

        // Пары финала (последний раунд перед финальными матчами).
        $finalPair = $this->pairParticipants($participants, $currentFieldSize);
        $finalists = $finalPair[0] ?? ['home' => null, 'away' => null];

        // === РАУНД ФИНАЛОВ ===
        $finalRoundMatches = [];

// ФИНАЛ - всегда 1 игра
        $finalMatch = [
            'match_number' => 1,
            'match_type' => 'final',
            'title' => 'Финал',
            'home_team' => $finalists['home']['team'] ?? null,
            'away_team' => $finalists['away']['team'] ?? null,
            'games' => [],
            'home_wins' => 0,
            'away_wins' => 0,
            'winner' => null,
            'status' => 'pending',
            'match_format' => 'single', // Всегда 1 игра
        ];

        if ($finalMatch['home_team'] && $finalMatch['away_team']) {
            $gamesBetween = $this->findAllGames($games, $finalMatch['home_team'], $finalMatch['away_team']);
            $seriesResult = $this->processSeries($gamesBetween, $finalMatch['home_team'], $finalMatch['away_team'], 'single'); // Всегда single

            $finalMatch['games'] = $seriesResult['games'];
            $finalMatch['home_wins'] = $seriesResult['home_wins'];
            $finalMatch['away_wins'] = $seriesResult['away_wins'];
            $finalMatch['winner'] = $seriesResult['winner'] ?
                ($seriesResult['winner']['id'] == $finalMatch['home_team']['id'] ? 'home' : 'away') : null;
            $finalMatch['status'] = $seriesResult['status'];
        } elseif ($finalMatch['home_team'] || $finalMatch['away_team']) {
            $finalMatch['status'] = 'pending';
        }

        $finalRoundMatches[] = $finalMatch;

// МАТЧ ЗА 3-Е МЕСТО - всегда 1 игра
        // Проигравшие берутся из двух матчей последнего сыгранного раунда
        // (для 4 команд это полуфиналы, для больших сеток — соответствующий раунд).
        $loser1 = isset($lastPlayedMatches[0]) ? $this->getLoserFromMatch($lastPlayedMatches[0]) : null;
        $loser2 = isset($lastPlayedMatches[1]) ? $this->getLoserFromMatch($lastPlayedMatches[1]) : null;

        $thirdPlaceMatch = [
            'match_number' => 2,
            'match_type' => 'third_place',
            'title' => 'Матч за 3-е место',
            'home_team' => $loser1,
            'away_team' => $loser2,
            'games' => [],
            'home_wins' => 0,
            'away_wins' => 0,
            'winner' => null,
            'status' => 'pending',
            'match_format' => 'single', // Всегда 1 игра
        ];

        if ($loser1 && $loser2) {
            $gamesBetween = $this->findAllGames($games, $loser1, $loser2);
            $seriesResult = $this->processSeries($gamesBetween, $loser1, $loser2, 'single'); // Всегда single

            $thirdPlaceMatch['games'] = $seriesResult['games'];
            $thirdPlaceMatch['home_wins'] = $seriesResult['home_wins'];
            $thirdPlaceMatch['away_wins'] = $seriesResult['away_wins'];
            $thirdPlaceMatch['winner'] = $seriesResult['winner'] ?
                ($seriesResult['winner']['id'] == $loser1['id'] ? 'home' : 'away') : null;
            $thirdPlaceMatch['status'] = $seriesResult['status'];
        } elseif ($loser1 || $loser2) {
            $thirdPlaceMatch['status'] = 'pending';
        }

        $finalRoundMatches[] = $thirdPlaceMatch;

        $bracket[] = [
            'round_number' => $entryRound,
            'round_name' => 'Финальные матчи',
            'matches' => $finalRoundMatches,
            'match_format' => 'single', // Всегда single
        ];

        // ОТЛАДКА - финальный результат
        //\Log::info('=== FINAL BRACKET ===');
        //\Log::info('Total rounds: ' . count($bracket));
        foreach ($bracket as $ri => $round) {
            //\Log::info('Round ' . ($ri+1) . ': ' . ($round['round_name'] ?? 'no name') . ', matches: ' . count($round['matches'] ?? []));
        }
        //\Log::info('======================');

        return $bracket;
    }

    /**
     * Ближайшая степень двойки, не меньшая заданного числа.
     */
    private function nextPowerOfTwo(int $value): int
    {
        $power = 2;
        while ($power < $value) {
            $power *= 2;
        }

        return $power;
    }

    /**
     * Порядок посевов по слотам сетки (олимпийская схема).
     * Для 8 слотов: [1, 8, 4, 5, 2, 7, 3, 6].
     */
    private function buildSeedOrder(int $slots): array
    {
        if ($slots <= 1) {
            return [1];
        }

        $prev = $this->buildSeedOrder(intdiv($slots, 2));
        $order = [];
        foreach ($prev as $seed) {
            $order[] = $seed;
            $order[] = $slots + 1 - $seed;
        }

        return $order;
    }

    /**
     * Формирует пары участников для раунда (соседние слоты сетки).
     */
    private function pairParticipants(array $participants, int $fieldSize): array
    {
        if ($fieldSize < 2) {
            return $participants
                ? [['home' => $participants[0], 'away' => null]]
                : [];
        }

        if ($fieldSize % 2 !== 0) {
            $participants[] = ['team' => null, 'type' => 'tbd'];
            $fieldSize++;
        }

        $pairs = [];
        for ($i = 0; $i < intdiv($fieldSize, 2); $i++) {
            $pairs[] = [
                'home' => $participants[$i * 2] ?? ['team' => null, 'type' => 'tbd'],
                'away' => $participants[$i * 2 + 1] ?? ['team' => null, 'type' => 'tbd'],
            ];
        }

        return $pairs;
    }

    /**
     * Находит все игры между двумя командами
     */
    private function findAllGames(Collection $games, ?array $homeTeam, ?array $awayTeam): Collection
    {
        if (!$homeTeam || !$awayTeam) {
            return collect();
        }

        return $games->filter(function ($game) use ($homeTeam, $awayTeam) {
            return ($game['home_team_id'] == $homeTeam['id'] && $game['away_team_id'] == $awayTeam['id']) ||
                ($game['home_team_id'] == $awayTeam['id'] && $game['away_team_id'] == $homeTeam['id']);
        })->values();
    }

    /**
     * Обрабатывает серию матчей между двумя командами
     */
    private function processSeries(Collection $games, ?array $homeTeam, ?array $awayTeam, string $format): array
    {
        $result = [
            'games' => [],
            'home_wins' => 0,
            'away_wins' => 0,
            'winner' => null,
            'status' => 'pending',
        ];

        if (!$homeTeam || !$awayTeam) {
            return $result;
        }

        $gamesArray = $games->values()->toArray();
        $neededWins = ($format === 'best_of_3') ? 2 : 1;
        $maxGames = ($format === 'best_of_3') ? 3 : 1;

        // Счет побед для каждой конкретной команды
        $homeTeamWins = 0;
        $awayTeamWins = 0;

        // Для каждого матча определяем, кто хозяин
        $homeTeamOrder = [];
        $awayTeamOrder = [];

        if ($format === 'best_of_3') {
            for ($i = 0; $i < 3; $i++) {
                $homeTeamOrder[$i] = ($i % 2 == 0) ? $homeTeam : $awayTeam;
                $awayTeamOrder[$i] = ($i % 2 == 0) ? $awayTeam : $homeTeam;
            }
        } else {
            $homeTeamOrder[0] = $homeTeam;
            $awayTeamOrder[0] = $awayTeam;
        }

        $allGamesProcessed = true;

        foreach ($gamesArray as $gameIndex => $game) {
            $actualHome = null;
            $actualAway = null;

            // Определяем фактические команды в игре
            if ($game['home_team_id'] == $homeTeam['id'] && $game['away_team_id'] == $awayTeam['id']) {
                $actualHome = $homeTeam;
                $actualAway = $awayTeam;
            } elseif ($game['home_team_id'] == $awayTeam['id'] && $game['away_team_id'] == $homeTeam['id']) {
                $actualHome = $awayTeam;
                $actualAway = $homeTeam;
            }

            if ($actualHome && $actualAway) {
                $homeScore = $game['home_score'];
                $awayScore = $game['away_score'];

                if ($homeScore !== null && $awayScore !== null) {
                    // Определяем победителя игры
                    $gameWinner = null;
                    if ($homeScore > $awayScore) {
                        $gameWinner = $actualHome;
                    } elseif ($awayScore > $homeScore) {
                        $gameWinner = $actualAway;
                    }

                    // Увеличиваем счет побед
                    if ($gameWinner && $gameWinner['id'] == $homeTeam['id']) {
                        $homeTeamWins++;
                    } elseif ($gameWinner && $gameWinner['id'] == $awayTeam['id']) {
                        $awayTeamWins++;
                    }

                    // Определяем, кто должен быть хозяином по расписанию
                    $scheduledHome = $homeTeamOrder[$gameIndex] ?? $homeTeam;
                    $scheduledAway = $awayTeamOrder[$gameIndex] ?? $awayTeam;

                    // Пересчитываем счета для отображения с учетом расписания
                    $displayHomeScore = $scheduledHome['id'] == $actualHome['id'] ? $homeScore : $awayScore;
                    $displayAwayScore = $scheduledAway['id'] == $actualAway['id'] ? $awayScore : $homeScore;

                    // Пересчитываем сеты для отображения
                    $displaySets = [];
                    foreach ($game['sets'] as $set) {
                        // Если расписанием хозяин совпадает с фактическим хозяином, то сеты не меняем
                        if ($scheduledHome['id'] == $actualHome['id']) {
                            $setHomeScore = $set['home_score'];
                            $setAwayScore = $set['away_score'];
                        } else {
                            // Если команды поменялись местами, меняем сеты местами
                            $setHomeScore = $set['away_score'];
                            $setAwayScore = $set['home_score'];
                        }

                        $displaySets[] = [
                            'set_number' => $set['set_number'],
                            'home_score' => $setHomeScore,
                            'away_score' => $setAwayScore,
                        ];
                    }

                    $result['games'][] = [
                        'game_id' => $game['id'],
                        'home_team' => $scheduledHome,
                        'away_team' => $scheduledAway,
                        'home_score' => $displayHomeScore,
                        'away_score' => $displayAwayScore,
                        'sets' => $displaySets,
                        'winner' => $gameWinner ? ($gameWinner['id'] == $scheduledHome['id'] ? 'home' : 'away') : null,
                    ];

                    // Проверяем победителя серии
                    if ($homeTeamWins >= $neededWins) {
                        $result['winner'] = $homeTeam;
                        $result['status'] = 'completed';
                        $result['home_wins'] = $homeTeamWins;
                        $result['away_wins'] = $awayTeamWins;
                        return $result;
                    } elseif ($awayTeamWins >= $neededWins) {
                        $result['winner'] = $awayTeam;
                        $result['status'] = 'completed';
                        $result['home_wins'] = $homeTeamWins;
                        $result['away_wins'] = $awayTeamWins;
                        return $result;
                    }
                } else {
                    // Игра еще не сыграна
                    $allGamesProcessed = false;

                    $scheduledHome = $homeTeamOrder[$gameIndex] ?? $homeTeam;
                    $scheduledAway = $awayTeamOrder[$gameIndex] ?? $awayTeam;

                    $result['games'][] = [
                        'game_id' => $game['id'],
                        'home_team' => $scheduledHome,
                        'away_team' => $scheduledAway,
                        'home_score' => null,
                        'away_score' => null,
                        'sets' => [],
                        'winner' => null,
                    ];

                    $result['status'] = 'scheduled';
                    break;
                }
            }
        }

        if ($allGamesProcessed && !$result['winner']) {
            $result['status'] = 'draw';
        } elseif (!$result['winner'] && $result['status'] !== 'scheduled') {
            $result['status'] = 'pending';
        }

        $result['home_wins'] = $homeTeamWins;
        $result['away_wins'] = $awayTeamWins;

        return $result;
    }

    private function getTeamsWithPositions(StageGroup $group): Collection
    {
        $teams = collect();

        foreach ($group->teams as $application) {
            if ($application->team) {
                $teams->push([
                    'id' => $application->team->id,
                    'application_id' => $application->id,
                    'name' => $application->team->name,
                    'position' => $application->pivot->position ?? null,
                ]);
            }
        }

        return $teams->sortBy('position')->values();
    }

    private function getGames(StageGroup $group): Collection
    {
        $games = Game::where('group_id', $group->id)
            ->with(['homeApplication.team', 'awayApplication.team', 'sets'])
            ->get();

        return $games->map(function ($game) {
            return [
                'id' => $game->id,
                'home_team_id' => $game->homeApplication?->team_id,
                'away_team_id' => $game->awayApplication?->team_id,
                'home_team_name' => $game->homeApplication?->team?->name,
                'away_team_name' => $game->awayApplication?->team?->name,
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
                'sets' => $game->sets->map(function ($set) {
                    return [
                        'set_number' => $set->set_number,
                        'home_score' => $set->home_score,
                        'away_score' => $set->away_score,
                    ];
                })->toArray(),
            ];
        });
    }

    private function findGame(Collection $games, ?array $homeTeam, ?array $awayTeam): ?array
    {
        if (!$homeTeam || !$awayTeam) {
            return null;
        }

        $game = $games->first(function ($game) use ($homeTeam, $awayTeam) {
            $match = ($game['home_team_id'] == $homeTeam['id'] && $game['away_team_id'] == $awayTeam['id']) ||
                ($game['home_team_id'] == $awayTeam['id'] && $game['away_team_id'] == $homeTeam['id']);

            return $match;
        });

        return $game;
    }

    private function getWinner(?array $game, ?array $homeTeam, ?array $awayTeam): ?array
    {
        if (!$game || !$homeTeam || !$awayTeam) {
            return null;
        }

        // Проверяем, что счет не null
        if ($game['home_score'] === null || $game['away_score'] === null) {
            return null;
        }

        if ($game['home_score'] > $game['away_score']) {
            return $homeTeam;
        } elseif ($game['away_score'] > $game['home_score']) {
            return $awayTeam;
        }

        return null;
    }

    private function getRoundNameByDistance(int $roundsLeftToFinal): string
    {
        $names = [
            1 => '1/2 финала',
            2 => '1/4 финала',
            3 => '1/8 финала',
            4 => '1/16 финала',
            5 => '1/32 финала',
        ];

        return $names[$roundsLeftToFinal] ?? 'Раунд';
    }

    private function getRoundName(int $matches): string
    {
        $names = [
            1 => 'Финал',
            2 => '1/2 финала',
            3 => '1/4 финала',
            4 => '1/4 финала',
            5 => '1/8 финала',
            6 => '1/8 финала',
            7 => '1/8 финала',
            8 => '1/8 финала',
            9 => '1/16 финала',
            10 => '1/16 финала',
            11 => '1/16 финала',
            12 => '1/16 финала',
            13 => '1/16 финала',
            14 => '1/16 финала',
            15 => '1/16 финала',
            16 => '1/16 финала',
        ];

        return $names[$matches] ?? "Раунд {$matches}";
    }

    // Функция для определения статуса матча
    private function getMatchStatus(?array $game, ?array $homeTeam, ?array $awayTeam): string
    {
        // Если нет обеих команд
        if (!$homeTeam && !$awayTeam) {
            return 'pending';
        }

        // Если есть игра
        if ($game && $game['home_score'] !== null && $game['away_score'] !== null) {
            return 'completed';
        }

        // Если игра запланирована
        return 'scheduled';
    }

    private function getLoserFromMatch(?array $match): ?array
    {
        if (!$match) return null;

        $homeTeam = $match['home_team'] ?? null;
        $awayTeam = $match['away_team'] ?? null;
        $winner = $match['winner'] ?? null;

        if (!$homeTeam || !$awayTeam) return null;

        if ($winner === 'home') {
            return $awayTeam;
        } elseif ($winner === 'away') {
            return $homeTeam;
        }

        return null;
    }
}

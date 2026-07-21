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
        $reverseSeeding = $config['reverse_seeding'] ?? false;
        $matchFormat = $config['match_format'] ?? 'single';

        // ОТЛАДКА
        //\Log::info('=== BRACKET GENERATE START ===');
        //\Log::info('Group ID: ' . $group->id);
        //\Log::info('Teams count: ' . $teams->count());
        //\Log::info('Bye positions: ' . json_encode($byePositions));
        //\Log::info('Match format: ' . $matchFormat);

        // Получаем все позиции команд
        $allPositions = $teams->pluck('position')->sort()->values()->toArray();
        //\Log::info('All positions: ' . json_encode($allPositions));
        $byePositions = array_values(array_intersect($allPositions, $byePositions));
        $regularPositions = array_values(array_diff($allPositions, $byePositions));
        //\Log::info('Regular positions: ' . json_encode($regularPositions));

        // Строим структуру
        $bracket = [];
        $winners = [];

        // === РАУНД 1 ===
        if (count($regularPositions) >= 2) {
            $sortedRegular = $regularPositions;
            if (!$reverseSeeding) {
                $half = count($sortedRegular) / 2;
                $firstHalf = array_slice($sortedRegular, 0, $half);
                $secondHalf = array_reverse(array_slice($sortedRegular, $half));
                $sortedRegular = [];
                for ($i = 0; $i < $half; $i++) {
                    $sortedRegular[] = $firstHalf[$i];
                    $sortedRegular[] = $secondHalf[$i];
                }
            }

            $matches = [];
            for ($i = 0; $i < count($sortedRegular) / 2; $i++) {
                $matches[] = [
                    'home_pos' => $sortedRegular[$i * 2],
                    'away_pos' => $sortedRegular[$i * 2 + 1],
                ];
            }

            $round1Matches = [];
            foreach ($matches as $index => $match) {
                $matchNumber = $index + 1;
                $homeTeam = $teams->firstWhere('position', $match['home_pos']);
                $awayTeam = $teams->firstWhere('position', $match['away_pos']);

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
                ];
            }

            $bracket[] = [
                'round_number' => 1,
                'round_name' => $this->getRoundName(count($regularPositions), 1, count($allPositions)),
                'matches' => $round1Matches,
                'match_format' => $matchFormat,
            ];
        }

        // === РАУНД 2 (полуфиналы) ===
        $round2Matches = [];

        $sortedBye = $byePositions;
        $firstRoundWinners = $winners[1] ?? [];
        $winnerCount = count($firstRoundWinners);

// Определяем сколько должно быть матчей в полуфинале
// Количество команд в полуфинале = BYE команды + количество матчей в 1-м раунде
        $totalRounds1Matches = count($round1Matches ?? []);
        $expectedSemifinalTeams = count($byePositions) + $totalRounds1Matches;
        $expectedSemifinalMatches = (int)ceil($expectedSemifinalTeams / 2);

// Собираем BYE команды
        $byeTeams = [];
        foreach ($sortedBye as $byePos) {
            $team = $teams->firstWhere('position', $byePos);
            if ($team) {
                $byeTeams[] = $team;
            }
        }

// Создаем список всех участников полуфиналов (победители матчей + BYE)
        $semifinalParticipants = [];

// Добавляем BYE команды
        foreach ($byeTeams as $byeTeam) {
            $semifinalParticipants[] = [
                'team' => $byeTeam,
                'type' => 'bye',
                'source' => 'bye'
            ];
        }

// Добавляем победителей из первого раунда (если есть) или создаем заглушки
        for ($i = 1; $i <= $totalRounds1Matches; $i++) {
            if (isset($firstRoundWinners[$i])) {
                $semifinalParticipants[] = [
                    'team' => $firstRoundWinners[$i],
                    'type' => 'winner',
                    'source' => 'match_' . $i,
                    'is_defined' => true
                ];
            } else {
                // Создаем заглушку для победителя, который еще не определен
                $semifinalParticipants[] = [
                    'team' => null,
                    'type' => 'tbd',
                    'source' => 'match_' . $i,
                    'is_defined' => false
                ];
            }
        }

// Сортируем: сначала BYE, потом победители в порядке матчей
        usort($semifinalParticipants, function($a, $b) {
            if ($a['type'] === 'bye' && $b['type'] !== 'bye') return -1;
            if ($a['type'] !== 'bye' && $b['type'] === 'bye') return 1;
            return 0;
        });

// Формируем пары: первый с последним, второй с предпоследним
        $paired = [];
        $count = count($semifinalParticipants);
        $half = (int)ceil($count / 2);

        for ($i = 0; $i < $half; $i++) {
            $home = $semifinalParticipants[$i] ?? null;
            $away = $semifinalParticipants[$count - 1 - $i] ?? null;

            // Если это одна и та же запись
            if ($home && $away && $home === $away) {
                $away = null;
            }

            $paired[] = ['home' => $home, 'away' => $away];
        }

        foreach ($paired as $index => $pair) {
            $matchNumber = $index + 1;
            $homeData = $pair['home'] ?? null;
            $awayData = $pair['away'] ?? null;

            $homeTeam = $homeData['team'] ?? null;
            $awayTeam = $awayData['team'] ?? null;

            // Если нет домашней команды, но есть гостевая - меняем местами
            if (!$homeTeam && $awayTeam) {
                $homeTeam = $awayTeam;
                $awayTeam = null;
            }

            // Если обе команды null - создаем пустой матч
            if (!$homeTeam && !$awayTeam) {
                $round2Matches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => null,
                    'away_team' => null,
                    'games' => [],
                    'home_wins' => 0,
                    'away_wins' => 0,
                    'winner' => null,
                    'status' => 'pending',
                    'match_format' => $matchFormat,
                    'is_bye' => false,
                ];
                continue;
            }

            // Если только одна команда (BYE без соперника)
            if ($homeTeam && !$awayTeam) {
                $round2Matches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => null,
                    'games' => [],
                    'home_wins' => 0,
                    'away_wins' => 0,
                    'winner' => null,
                    'status' => 'pending',
                    'match_format' => $matchFormat,
                    'is_bye' => true,
                ];
                continue;
            }

            // Обе команды есть - обрабатываем как обычно
            $gamesBetween = $this->findAllGames($games, $homeTeam, $awayTeam);
            $seriesResult = $this->processSeries($gamesBetween, $homeTeam, $awayTeam, $matchFormat);

            $winner = $seriesResult['winner'] ?? null;
            if ($winner) {
                $winners[2][$matchNumber] = $winner;
            }

            $round2Matches[] = [
                'match_number' => $matchNumber,
                'home_team' => $homeTeam,
                'away_team' => $awayTeam,
                'games' => $seriesResult['games'],
                'home_wins' => $seriesResult['home_wins'],
                'away_wins' => $seriesResult['away_wins'],
                'winner' => $winner ? ($winner['id'] == ($homeTeam['id'] ?? null) ? 'home' : 'away') : null,
                'status' => $seriesResult['status'],
                'match_format' => $matchFormat,
                'is_bye' => false,
            ];
        }

        $bracket[] = [
            'round_number' => 2,
            'round_name' => $this->getRoundName($expectedSemifinalTeams, 2, count($allPositions)),
            'matches' => $round2Matches,
            'match_format' => $matchFormat,
        ];

        // === РАУНД 3 (финалы) ===
        $finalRoundMatches = [];

// ФИНАЛ - всегда 1 игра
        $finalMatch = [
            'match_number' => 1,
            'match_type' => 'final',
            'title' => 'Финал',
            'home_team' => $winners[2][1] ?? null,
            'away_team' => $winners[2][2] ?? null,
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
        $loser1 = isset($round2Matches[0]) ? $this->getLoserFromMatch($round2Matches[0]) : null;
        $loser2 = isset($round2Matches[1]) ? $this->getLoserFromMatch($round2Matches[1]) : null;

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
            'round_number' => 3,
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

    private function getRoundName(int $teams, int $roundNumber = 1, int $totalTeams = 0): string
    {
        // Для первого раунда с BYE (когда обычных команд меньше, чем общее количество)
        if ($roundNumber == 1 && $teams < $totalTeams) {
            $remainingTeams = $teams;
            $names = [
                2 => '1/2 финала',
                4 => '1/4 финала',
                6 => '1/4 финала',
                8 => '1/8 финала',
                10 => '1/10 финала',
                12 => '1/12 финала',
                14 => '1/14 финала',
                16 => '1/16 финала',
            ];
            return $names[$teams] ?? "Раунд 1";
        }

        // Для остальных раундов
        $names = [
            2 => 'Финал',
            4 => '1/2 финала',
            8 => '1/4 финала',
            16 => '1/8 финала',
            32 => '1/16 финала',
        ];

        return $names[$teams] ?? "Раунд";
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

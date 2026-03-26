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

        // Получаем все позиции команд
        $allPositions = $teams->pluck('position')->sort()->values()->toArray();
        $byePositions = array_values(array_intersect($allPositions, $byePositions));
        $regularPositions = array_values(array_diff($allPositions, $byePositions));

        // Строим структуру
        $bracket = [];
        $winners = []; // [round][match] = team

        // === РАУНД 1: игры между обычными командами ===
        if (count($regularPositions) >= 2) {
            // Сортируем по принципу "сильный со слабым"
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
                $game = $this->findGame($games, $homeTeam, $awayTeam);

                // Определяем победителя
                $winner = $this->getWinner($game, $homeTeam, $awayTeam);
                if ($winner) {
                    $winners[1][$matchNumber] = $winner;
                }

                $round1Matches[] = [
                    'match_number' => $matchNumber,
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'home_score' => $game['home_score'] ?? null,
                    'away_score' => $game['away_score'] ?? null,
                    'sets' => $game['sets'] ?? [],
                    'winner' => $winner ? ($winner['id'] == $homeTeam['id'] ? 'home' : 'away') : null,
                    'status' => $this->getMatchStatus($game, $homeTeam, $awayTeam),
                ];
            }

            $bracket[] = [
                'round_number' => 1,
                'round_name' => $this->getRoundName(count($regularPositions), 1, count($allPositions)),
                'matches' => $round1Matches,
            ];
        }

        // Раунд 2: BYE команды + победители 1-го раунда
        $round2Matches = [];

// Сортируем BYE команды (сильнейшие BYE должны быть в разных полуфиналах)
        $sortedBye = $byePositions;
        if (!$reverseSeeding) {
            //$sortedBye = array_reverse($sortedBye);
            $sortedBye = $sortedBye;
        }

// Получаем победителей 1-го раунда
        $firstRoundWinners = $winners[1] ?? [];
        $winnerCount = count($firstRoundWinners);
        $winnerNumbers = array_keys($firstRoundWinners);

// Определяем, сколько матчей в полуфинале
        $totalTeamsInSemifinal = count($byePositions) + $winnerCount;
        $matchesInSemifinal = $totalTeamsInSemifinal / 2;

// Формируем пары в зависимости от количества команд
        for ($i = 0; $i < $matchesInSemifinal; $i++) {
            $matchNumber = $i + 1;
            $homeTeam = null;
            $awayTeam = null;

            if ($winnerCount == 2) {
                // Для 6 команд (победителей 2 + BYE 2)
                if ($i == 0) {
                    // Первый полуфинал: BYE1 (сильнейший) vs победитель матча 2 (слабая пара 4-5)
                    if (isset($sortedBye[0])) {
                        $homeTeam = $teams->firstWhere('position', $sortedBye[0]);
                    }
                    $awayTeam = $firstRoundWinners[2] ?? null; // победитель 4-5
                } else {
                    // Второй полуфинал: BYE2 (слабейший) vs победитель матча 1 (сильная пара 3-6)
                    if (isset($sortedBye[1])) {
                        $homeTeam = $teams->firstWhere('position', $sortedBye[1]);
                    }
                    $awayTeam = $firstRoundWinners[1] ?? null; // победитель 3-6
                }
            } elseif ($winnerCount == 4) {
                // Для 8 команд (победителей 4)
                if ($i == 0) {
                    // Первый полуфинал: победитель матча 1 (1-8) vs победитель матча 4 (4-5)
                    $homeTeam = $firstRoundWinners[1] ?? null;
                    $awayTeam = $firstRoundWinners[4] ?? null;
                } else {
                    // Второй полуфинал: победитель матча 2 (2-7) vs победитель матча 3 (3-6)
                    $homeTeam = $firstRoundWinners[2] ?? null;
                    $awayTeam = $firstRoundWinners[3] ?? null;
                }
            }

            // Если одна из команд отсутствует, это BYE в этом раунде
            if (!$homeTeam && $awayTeam) {
                $homeTeam = $awayTeam;
                $awayTeam = null;
            }

            $game = $this->findGame($games, $homeTeam, $awayTeam);

            $winner = $this->getWinner($game, $homeTeam, $awayTeam);
            if ($winner) {
                $winners[2][$matchNumber] = $winner;
            }

            $round2Matches[] = [
                'match_number' => $matchNumber,
                'home_team' => $homeTeam,
                'away_team' => $awayTeam,
                'home_score' => $game['home_score'] ?? null,
                'away_score' => $game['away_score'] ?? null,
                'sets' => $game['sets'] ?? [],
                'winner' => $winner ? ($winner['id'] == ($homeTeam['id'] ?? null) ? 'home' : 'away') : null,
                'status' => $this->getMatchStatus($game, $homeTeam, $awayTeam),
            ];
        }

        $bracket[] = [
            'round_number' => 2,
            'round_name' => $this->getRoundName($totalTeamsInSemifinal),
            'matches' => $round2Matches,
        ];

        // === ФИНАЛЬНЫЙ РАУНД (финал + матч за 3-е место) ===
        $finalRoundMatches = [];

// Сначала ФИНАЛ
        $finalMatch = [
            'match_number' => 1,
            'match_type' => 'final',
            'title' => 'Финал',
            'home_team' => null,
            'away_team' => null,
            'home_score' => null,
            'away_score' => null,
            'sets' => [],
            'winner' => null,
            'status' => 'pending'
        ];

// Если есть победители полуфиналов, заполняем
        if (isset($winners[2][1])) {
            $finalMatch['home_team'] = $winners[2][1];
        }
        if (isset($winners[2][2])) {
            $finalMatch['away_team'] = $winners[2][2];
        }

// Если оба финалиста определены, ищем игру между ними
        if ($finalMatch['home_team'] && $finalMatch['away_team']) {
            $game = $this->findGame($games, $finalMatch['home_team'], $finalMatch['away_team']);
            if ($game) {
                $finalMatch['home_score'] = $game['home_score'];
                $finalMatch['away_score'] = $game['away_score'];
                $finalMatch['sets'] = $game['sets'];
                $finalMatch['status'] = $this->getMatchStatus($game, $finalMatch['home_team'], $finalMatch['away_team']);

                $winner = $this->getWinner($game, $finalMatch['home_team'], $finalMatch['away_team']);
                if ($winner) {
                    $finalMatch['winner'] = $winner['id'] == $finalMatch['home_team']['id'] ? 'home' : 'away';
                }
            }
        }

        $finalRoundMatches[] = $finalMatch;

// Затем МАТЧ ЗА 3-Е МЕСТО
        if (isset($winners[2][1]) && isset($winners[2][2])) {
            // Проигравшие в полуфиналах
            $loser1 = $this->getLoserFromMatch($round2Matches[0] ?? null);
            $loser2 = $this->getLoserFromMatch($round2Matches[1] ?? null);

            $thirdPlaceMatch = [
                'match_number' => 2,
                'match_type' => 'third_place',
                'title' => 'Матч за 3-е место',
                'home_team' => $loser1,
                'away_team' => $loser2,
                'home_score' => null,
                'away_score' => null,
                'sets' => [],
                'winner' => null,
                'status' => 'pending'
            ];

            // Ищем игру за 3-е место
            if ($loser1 && $loser2) {
                $game = $this->findGame($games, $loser1, $loser2);
                if ($game) {
                    $thirdPlaceMatch['home_score'] = $game['home_score'];
                    $thirdPlaceMatch['away_score'] = $game['away_score'];
                    $thirdPlaceMatch['sets'] = $game['sets'];
                    $thirdPlaceMatch['status'] = $this->getMatchStatus($game, $loser1, $loser2);

                    $winner = $this->getWinner($game, $loser1, $loser2);
                    if ($winner) {
                        $thirdPlaceMatch['winner'] = $winner['id'] == $loser1['id'] ? 'home' : 'away';
                    }
                }
            }

            $finalRoundMatches[] = $thirdPlaceMatch;
        }

        $bracket[] = [
            'round_number' => 3,
            'round_name' => 'Финальные матчи',
            'matches' => $finalRoundMatches,
        ];

        return $bracket;
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
        // Для первого раунда с BYE
        if ($teams == 4 && $roundNumber == 1 && $totalTeams > 4) {
            return '1/4 финала';
        }

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

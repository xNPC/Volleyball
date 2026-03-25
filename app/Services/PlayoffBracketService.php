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

        // === РАУНД 2: BYE команды + победители 1-го раунда ===
        $round2Matches = [];

        // Сортируем BYE команды
        $sortedBye = $byePositions;
        if (!$reverseSeeding) {
            $sortedBye = array_reverse($sortedBye);
        }

        // Определяем, сколько матчей в полуфинале
        $totalTeamsInSemifinal = count($byePositions) + count($winners[1] ?? []);
        $matchesInSemifinal = $totalTeamsInSemifinal / 2;

        for ($i = 0; $i < $matchesInSemifinal; $i++) {
            $matchNumber = $i + 1;
            $homeTeam = null;
            $awayTeam = null;

            // Определяем, кто участвует в этом матче
            if (isset($sortedBye[$i])) {
                $homeTeam = $teams->firstWhere('position', $sortedBye[$i]);
            } else {
                $winnerIndex = $i - count($sortedBye) + 1;
                $homeTeam = $winners[1][$winnerIndex] ?? null;
            }

            $awayIndex = $matchesInSemifinal + $i;
            if (isset($sortedBye[$awayIndex])) {
                $awayTeam = $teams->firstWhere('position', $sortedBye[$awayIndex]);
            } else {
                $winnerIndex = $awayIndex - count($sortedBye) + 1;
                $awayTeam = $winners[1][$winnerIndex] ?? null;
            }

            // Если одна из команд отсутствует, это BYE в этом раунде
            if (!$homeTeam && $awayTeam) {
                $homeTeam = $awayTeam;
                $awayTeam = null;
            }

            $game = $this->findGame($games, $homeTeam, $awayTeam);

            // Определяем победителя
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

        // === РАУНД 3: Финал ===
        $finalMatch = [
            'match_number' => 1,
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

                $winner = $this->getWinner($game, $finalMatch['home_team'], $finalMatch['away_team']);
                if ($winner) {
                    $finalMatch['winner'] = $winner['id'] == $finalMatch['home_team']['id'] ? 'home' : 'away';
                }
            }
            $finalMatch['status'] = $this->getMatchStatus($game, $finalMatch['home_team'], $finalMatch['away_team']);
        }

        $bracket[] = [
            'round_number' => 3,
            'round_name' => 'Финал',
            'matches' => [$finalMatch],
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

        // Отладка
        \Log::info('Игры в группе ' . $group->id, [
            'count' => $games->count(),
            'games' => $games->map(function($game) {
                return [
                    'id' => $game->id,
                    'home_team' => $game->homeApplication?->team?->name,
                    'away_team' => $game->awayApplication?->team?->name,
                    'home_score' => $game->home_score,
                    'away_score' => $game->away_score,
                    'status' => $game->status,
                ];
            })->toArray()
        ]);

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

            if ($match) {
                \Log::info('Найдена игра', [
                    'home_team' => $game['home_team_name'],
                    'away_team' => $game['away_team_name'],
                    'home_score' => $game['home_score'],
                    'away_score' => $game['away_score'],
                ]);
            }

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

        \Log::info('Определяем победителя', [
            'home' => $homeTeam['name'],
            'away' => $awayTeam['name'],
            'home_score' => $game['home_score'],
            'away_score' => $game['away_score'],
        ]);

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
}

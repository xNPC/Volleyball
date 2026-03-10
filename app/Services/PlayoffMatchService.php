<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameSet;
use Illuminate\Support\Collection;

class PlayoffMatchService
{
    /**
     * Создает серию матчей для одной пары
     */
    public function createMatchSeries(array $matchConfig, $homeTeam, $awayTeam, $stage): array
    {
        $series = [
            'match_id' => $matchConfig['match_number'],
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'home_wins' => 0,
            'away_wins' => 0,
            'home_points' => 0,
            'away_points' => 0,
            'home_sets' => 0,
            'away_sets' => 0,
            'games' => [],
            'status' => 'scheduled',
            'winner' => null,
            'next_match' => $matchConfig['next_match'] ?? null,
        ];

        // Определяем количество игр в серии
        $gamesCount = $this->getGamesCountForSeries($matchConfig);

        for ($i = 1; $i <= $gamesCount; $i++) {
            $series['games'][] = [
                'game_number' => $i,
                'home_score' => null,
                'away_score' => null,
                'sets' => [],
                'status' => 'scheduled',
                'is_deciding' => ($i === $gamesCount),
            ];
        }

        return $series;
    }

    /**
     * Обновляет серию после игры
     */
    public function updateMatchSeries(array $series, array $gameResult): array
    {
        $gameIndex = $gameResult['game_number'] - 1;

        // Обновляем результат игры
        $series['games'][$gameIndex] = array_merge(
            $series['games'][$gameIndex],
            $gameResult
        );
        $series['games'][$gameIndex]['status'] = 'completed';

        // Обновляем статистику серии
        if ($gameResult['home_score'] > $gameResult['away_score']) {
            $series['home_wins']++;
        } else {
            $series['away_wins']++;
        }

        $series['home_points'] += $gameResult['home_score'];
        $series['away_points'] += $gameResult['away_score'];

        // Обновляем сеты
        foreach ($gameResult['sets'] as $set) {
            if ($set['home_score'] > $set['away_score']) {
                $series['home_sets']++;
            } else {
                $series['away_sets']++;
            }
        }

        // Проверяем, определился ли победитель серии
        $winner = $this->checkSeriesWinner($series);

        if ($winner) {
            $series['winner'] = $winner;
            $series['status'] = 'completed';

            // Создаем следующую игру если нужно
            if ($this->needsDecidingGame($series)) {
                $series = $this->addDecidingGame($series);
            }
        }

        return $series;
    }

    /**
     * Проверяет, определился ли победитель серии
     */
    private function checkSeriesWinner(array $series): ?string
    {
        $config = $series['match_config'] ?? ['best_of' => 1];
        $bestOf = $config['best_of'] ?? 1;

        $neededWins = ceil($bestOf / 2);

        if ($series['home_wins'] >= $neededWins) {
            return 'home';
        }

        if ($series['away_wins'] >= $neededWins) {
            return 'away';
        }

        return null;
    }

    /**
     * Определяет количество игр в серии
     */
    private function getGamesCountForSeries(array $matchConfig): int
    {
        return $matchConfig['best_of'] ?? 1;
    }

    /**
     * Проверяет, нужен ли золотой сет
     */
    private function needsDecidingGame(array $series): bool
    {
        $config = $series['match_config'] ?? [];
        $tieBreaker = $config['tie_breaker'] ?? 'golden_set';

        if ($tieBreaker === 'golden_set' && $series['home_wins'] === $series['away_wins']) {
            return true;
        }

        return false;
    }

    /**
     * Добавляет решающую игру
     */
    private function addDecidingGame(array $series): array
    {
        $series['games'][] = [
            'game_number' => count($series['games']) + 1,
            'home_score' => null,
            'away_score' => null,
            'sets' => [],
            'status' => 'scheduled',
            'is_deciding' => true,
            'is_golden_set' => true,
        ];

        return $series;
    }
}

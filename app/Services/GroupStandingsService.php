<?php

namespace App\Services;

use App\Models\StageGroup;
use App\Models\TournamentApplication;
use Illuminate\Support\Collection;

class GroupStandingsService
{
    public function calculateStandings(StageGroup $group): Collection
    {
        $teams = $group->teams->load('team');
        $games = $group->games()->with('sets')->get();

        $standings = collect();

        foreach ($teams as $teamApplication) {
            $teamStats = $this->calculateTeamStats($teamApplication, $games, $teams);
            $standings->push($teamStats);
        }

        return $this->sortStandings($standings);
    }

    private function calculateTeamStats(TournamentApplication $team, $games, $teams): array
    {
        $stats = [
            'team' => $team,
            'team_name' => $team->team->name,
            'games_played' => 0,
            'games_won' => 0,
            'games_lost' => 0,
            'points' => 0,
            'sets_won' => 0,
            'sets_lost' => 0,
            'points_won' => 0,
            'points_lost' => 0,
            'results' => []
        ];

        // Инициализируем результаты против всех команд
        foreach ($teams as $opponent) {
            $stats['results'][$opponent->id] = null;
        }

        // Анализируем игры
        foreach ($games as $game) {
            if ($this->isTeamInGame($team, $game)) {
                $this->processGame($team, $game, $stats);
            }
        }

        // Рассчитываем коэффициенты
        $stats['sets_ratio'] = $stats['sets_lost'] > 0
            ? round($stats['sets_won'] / $stats['sets_lost'], 3)
            : ($stats['sets_won'] > 0 ? $stats['sets_won'] : 0);

        $stats['points_ratio'] = $stats['points_lost'] > 0
            ? round($stats['points_won'] / $stats['points_lost'], 3)
            : ($stats['points_won'] > 0 ? $stats['points_won'] : 0);

        return $stats;
    }

    private function isTeamInGame(TournamentApplication $team, $game): bool
    {
        return $game->home_application_id === $team->id ||
            $game->away_application_id === $team->id;
    }

    private function processGame(TournamentApplication $team, $game, array &$stats): void
    {
        if ($game->home_score === null || $game->away_score === null) {
            return; // Пропускаем игры без результата
        }

        $isHome = $game->home_application_id === $team->id;
        $opponentId = $isHome ? $game->away_application_id : $game->home_application_id;

        $teamScore = $isHome ? $game->home_score : $game->away_score;
        $opponentScore = $isHome ? $game->away_score : $game->home_score;

        // Проверяем на техническое поражение
        $isTechnicalDefeat = $this->isTechnicalDefeat($game, $isHome);

        // Обновляем общую статистику
        $stats['games_played']++;

        if ($isTechnicalDefeat) {
            // Техническое поражение: -1 очко
            $stats['points'] -= 1;
            $stats['games_lost']++;
            $resultClass = 'technical-defeat';
        } elseif ($teamScore > $opponentScore) {
            // Победа
            $stats['games_won']++;

            // Определяем количество очков за победу
            if ($teamScore == 3 && $opponentScore == 2) {
                $stats['points'] += 2; // Победа 3:2 = 2 очка
            } else {
                $stats['points'] += 3; // Победа 3:0 или 3:1 = 3 очка
            }
            $resultClass = 'win-score';
        } else {
            // Поражение
            $stats['games_lost']++;

            // Определяем количество очков за поражение
            if ($teamScore == 2 && $opponentScore == 3) {
                $stats['points'] += 1; // Поражение 2:3 = 1 очко
            } else {
                $stats['points'] += 0; // Поражение 0:3 или 1:3 = 0 очков
            }
            $resultClass = 'lose-score';
        }

        // Сохраняем результат против оппонента
        $stats['results'][$opponentId] = [
            'score' => $isHome ? "{$game->home_score}:{$game->away_score}" : "{$game->away_score}:{$game->home_score}",
            'class' => $resultClass,
            'is_home' => $isHome,
            'is_technical' => $isTechnicalDefeat
        ];

        // Анализируем сеты
        foreach ($game->sets as $set) {
            $teamSetScore = $isHome ? $set->home_score : $set->away_score;
            $opponentSetScore = $isHome ? $set->away_score : $set->home_score;

            $stats['sets_won'] += $teamSetScore > $opponentSetScore ? 1 : 0;
            $stats['sets_lost'] += $teamSetScore < $opponentSetScore ? 1 : 0;

            $stats['points_won'] += $teamSetScore;
            $stats['points_lost'] += $opponentSetScore;
        }
    }

    private function isTechnicalDefeat($game, bool $isHome): bool
    {
        // Проверяем все сеты на техническое поражение (0:25 в трех партиях)
        $technicalSets = 0;

        foreach ($game->sets as $set) {
            $teamScore = $isHome ? $set->home_score : $set->away_score;
            $opponentScore = $isHome ? $set->away_score : $set->home_score;

            if ($teamScore == 0 && $opponentScore == 25) {
                $technicalSets++;
            }
        }

        // Если три технических сета - это техническое поражение
        return $technicalSets >= 3;
    }

    private function sortStandings(Collection $standings): Collection
    {
        return $standings->sort(function ($a, $b) {
            // Сначала по количеству побед
            if ($a['games_won'] != $b['games_won']) {
                return $b['games_won'] <=> $a['games_won'];
            }

            // Затем по очкам
            if ($a['points'] != $b['points']) {
                return $b['points'] <=> $a['points'];
            }

            // Затем по коэффициенту партий
            if ($a['sets_ratio'] != $b['sets_ratio']) {
                return $b['sets_ratio'] <=> $a['sets_ratio'];
            }

            // Затем по коэффициенту мячей
            if ($a['points_ratio'] != $b['points_ratio']) {
                return $b['points_ratio'] <=> $a['points_ratio'];
            }

            // При полном равенстве - по алфавиту
            return $a['team_name'] <=> $b['team_name'];
        })->values();
    }
}

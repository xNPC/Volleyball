<?php

namespace App\Services;

use App\Models\StageGroup;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use Illuminate\Support\Collection;

class GroupStandingsService
{
    // Типы волейбола
    const TYPE_INDOOR = 'indoor'; // Классический (до 5 партий)
    const TYPE_BEACH = 'beach';   // Пляжный (до 3 партий)

    /**
     * Рассчитывает турнирную таблицу для группы
     */
    public function calculateStandings(StageGroup $group): Collection
    {
        $teams = $group->teams->load('team');
        $games = $group->games()->with('sets')->get();

        // Определяем тип турнира через группу -> этап -> турнир
        $volleyballType = $this->getVolleyballType($group);

        $standings = collect();

        foreach ($teams as $teamApplication) {
            $teamStats = $this->calculateTeamStats($teamApplication, $games, $teams, $volleyballType);
            $standings->push($teamStats);
        }

        return $this->sortStandings($standings);
    }

    /**
     * Определяет тип волейбола для группы (через цепочку: группа -> этап -> турнир)
     */
    private function getVolleyballType(StageGroup $group): string
    {
        // Пытаемся получить тип волейбола из турнира
        if ($group->stage && $group->stage->tournament && isset($group->stage->tournament->volleyball_type)) {
            return $group->stage->tournament->volleyball_type;
        }

        // Если не нашли - по умолчанию классический волейбол
        return self::TYPE_INDOOR;
    }

    private function calculateTeamStats(TournamentApplication $team, $games, $teams, string $volleyballType): array
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
                $this->processGame($team, $game, $stats, $volleyballType);
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

    private function processGame(TournamentApplication $team, $game, array &$stats, string $volleyballType): void
    {
        if ($game->home_score === null || $game->away_score === null) {
            return; // Пропускаем игры без результата
        }

        $isHome = $game->home_application_id === $team->id;
        $opponentId = $isHome ? $game->away_application_id : $game->home_application_id;

        $teamScore = $isHome ? $game->home_score : $game->away_score;
        $opponentScore = $isHome ? $game->away_score : $game->home_score;

        // Проверяем на техническое поражение
        $isTechnicalDefeat = $this->isTechnicalDefeat($game, $isHome, $volleyballType);

        // Обновляем общую статистику
        $stats['games_played']++;

        if ($isTechnicalDefeat) {
            // Техническое поражение: -1 очко
            $stats['points'] -= 1;
            $stats['games_lost']++;
            $resultClass = 'technical-defeat';
        } else {
            // Подсчет очков в зависимости от типа волейбола
            $pointsInfo = $this->calculatePoints($teamScore, $opponentScore, $volleyballType);
            $stats['points'] += $pointsInfo['points'];

            if ($pointsInfo['is_win']) {
                $stats['games_won']++;
                $resultClass = 'win-score';
            } else {
                $stats['games_lost']++;
                $resultClass = 'lose-score';
            }
        }

        // Сохраняем результат против оппонента
        $stats['results'][$opponentId] = [
            'score' => $isHome ? "{$game->home_score}:{$game->away_score}" : "{$game->away_score}:{$game->home_score}",
            'class' => $resultClass,
            'is_home' => $isHome,
            'is_technical' => $isTechnicalDefeat ?? false
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

    /**
     * Подсчет очков в зависимости от типа волейбола
     */
    private function calculatePoints(int $teamScore, int $opponentScore, string $volleyballType): array
    {
        if ($volleyballType === self::TYPE_BEACH) {
            // Пляжный волейбол
            if ($teamScore > $opponentScore) {
                // Победа
                if ($teamScore == 2 && $opponentScore == 0) {
                    return ['points' => 3, 'is_win' => true]; // 2:0 - 3 очка
                } elseif ($teamScore == 2 && $opponentScore == 1) {
                    return ['points' => 2, 'is_win' => true]; // 2:1 - 2 очка
                }
            } else {
                // Поражение
                if ($teamScore == 1 && $opponentScore == 2) {
                    return ['points' => 1, 'is_win' => false]; // 1:2 - 1 очко
                } elseif ($teamScore == 0 && $opponentScore == 2) {
                    return ['points' => 0, 'is_win' => false]; // 0:2 - 0 очков
                }
            }

            // Fallback (если вдруг другие значения)
            return ['points' => 0, 'is_win' => $teamScore > $opponentScore];

        } else {
            // Классический волейбол
            if ($teamScore > $opponentScore) {
                // Победа
                if ($teamScore == 3 && $opponentScore == 2) {
                    return ['points' => 2, 'is_win' => true]; // 3:2 - 2 очка
                } else {
                    return ['points' => 3, 'is_win' => true]; // 3:0 или 3:1 - 3 очка
                }
            } else {
                // Поражение
                if ($teamScore == 2 && $opponentScore == 3) {
                    return ['points' => 1, 'is_win' => false]; // 2:3 - 1 очко
                } else {
                    return ['points' => 0, 'is_win' => false]; // 0:3 или 1:3 - 0 очков
                }
            }
        }
    }

    private function isTechnicalDefeat($game, bool $isHome, string $volleyballType): bool
    {
        $technicalSets = 0;

        // Максимальное количество сетов для технического поражения
        $maxSets = $volleyballType === self::TYPE_BEACH ? 2 : 3;

        foreach ($game->sets as $set) {
            $teamScore = $isHome ? $set->home_score : $set->away_score;
            $opponentScore = $isHome ? $set->away_score : $set->home_score;

            // Проверяем на техническое поражение (0:21 для пляжного, 0:25 для классического)
            $technicalScore = $volleyballType === self::TYPE_BEACH ? 21 : 25;

            if ($teamScore == 0 && $opponentScore == $technicalScore) {
                $technicalSets++;
            }
        }

        // Если нужное количество технических сетов - это техническое поражение
        return $technicalSets >= $maxSets;
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

    /**
     * Рассчитывает таблицы для всех групп этапа
     */
    public function calculateStandingsForStage(TournamentStage $stage): Collection
    {
        $groupsWithStandings = collect();

        foreach ($stage->groups as $group) {
            $group->standings = $this->calculateStandings($group);
            $groupsWithStandings->push($group);
        }

        return $groupsWithStandings;
    }

    /**
     * Рассчитывает очки для конкретного матча (для отображения)
     */
    public function calculateMatchPoints(int $homeScore, int $awayScore, ?string $volleyballType = null): array
    {
        $type = $volleyballType ?? self::TYPE_INDOOR;

        $homePoints = $this->calculatePoints($homeScore, $awayScore, $type);
        $awayPoints = $this->calculatePoints($awayScore, $homeScore, $type);

        return [
            'home_points' => $homePoints['points'],
            'away_points' => $awayPoints['points'],
            'home_win' => $homePoints['is_win'],
            'away_win' => $awayPoints['is_win']
        ];
    }
}

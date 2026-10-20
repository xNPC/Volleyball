<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameSet;
use App\Models\PlayoffConfig;
use App\Models\StageGroup;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use App\Models\Venue;
use App\Services\PlayoffBracketService;
use Illuminate\Database\Seeder;

class PlayoffShowcaseSeeder extends Seeder
{
    private Venue $venue;

    public function run(): void
    {
        $this->venue = Venue::inRandomOrder()->firstOrFail();

        $tournament = Tournament::updateOrCreate(
            ['name' => 'Демо: варианты плейофф'],
            [
                'organization_id' => 1,
                'volleyball_type' => 'indoor',
                'description' => 'Демонстрационные сетки плейофф с разным числом команд и BYE.',
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-05',
                'status' => 'ongoing',
                'rules' => 'Демонстрация различных форматов плейофф.',
                'needs_venue' => true,
            ]
        );

        $variants = [
            ['teams' => 4, 'bye' => [], 'format' => 'single', 'label' => '4 команды (без BYE)'],
            ['teams' => 8, 'bye' => [], 'format' => 'single', 'label' => '8 команд (без BYE)'],
            ['teams' => 6, 'bye' => [1, 2], 'format' => 'single', 'label' => '6 команд (2 BYE)'],
            ['teams' => 7, 'bye' => [1], 'format' => 'single', 'label' => '7 команд (1 BYE)'],
            ['teams' => 8, 'bye' => [], 'format' => 'best_of_3', 'label' => '8 команд (до 2 побед)'],
        ];

        $labels = array_column($variants, 'label');
        $stale = $tournament->stages()->whereNotIn('name', $labels)->get();
        foreach ($stale as $stage) {
            foreach (Game::where('stage_id', $stage->id)->get() as $game) {
                $game->sets()->delete();
                $game->delete();
            }
            PlayoffConfig::where('stage_id', $stage->id)->delete();
            StageGroup::where('stage_id', $stage->id)->delete();
            $stage->delete();
        }

        foreach ($variants as $index => $variant) {
            $this->seedVariant($tournament, $variant, $index + 1);
        }
    }

    private function seedVariant(Tournament $tournament, array $variant, int $order): void
    {
        $teamCount = $variant['teams'];
        $byePositions = $variant['bye'];
        $matchFormat = $variant['format'];

        $stage = TournamentStage::updateOrCreate(
            ['tournament_id' => $tournament->id, 'name' => $variant['label']],
            [
                'stage_type' => 'playoff',
                'order' => $order,
                'start_date' => '2026-11-02',
                'end_date' => '2026-11-04',
            ]
        );

        $group = StageGroup::updateOrCreate(
            ['stage_id' => $stage->id, 'name' => 'Плейофф'],
            ['order' => 1, 'team_count' => $teamCount, 'is_active' => true]
        );

        $applications = $this->resolveApplications($tournament, $teamCount);

        $group->teams()->detach();
        foreach ($applications as $position => $application) {
            $group->teams()->attach($application->id, ['position' => $position + 1]);
        }

        Game::where('stage_id', $stage->id)->get()->each(function (Game $game) {
            $game->sets()->delete();
            $game->delete();
        });

        PlayoffConfig::updateOrCreate(
            ['stage_id' => $stage->id, 'group_id' => $group->id],
            [
                'total_teams' => $teamCount,
                'bye_positions' => $byePositions,
                'match_format' => $matchFormat,
            ]
        );

        $this->generateBracketGames($stage, $group, $matchFormat);
    }

    private function resolveApplications(Tournament $tournament, int $teamCount): array
    {
        $existing = TournamentApplication::where('tournament_id', $tournament->id)
            ->orderBy('id')
            ->get();

        while ($existing->count() < $teamCount) {
            $number = Team::count() + 1;
            $team = Team::create(['name' => "Демо-команда {$number}"]);
            $existing->push(TournamentApplication::create([
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'status' => 'approved',
            ]));
        }

        return $existing->take($teamCount)->values()->all();
    }

    private function generateBracketGames(TournamentStage $stage, StageGroup $group, string $matchFormat): void
    {
        $service = app(PlayoffBracketService::class);
        $config = PlayoffConfig::where('stage_id', $stage->id)
            ->where('group_id', $group->id)
            ->first()
            ->toArray();

        $scheduled = $this->venue->id;
        $time = '2026-11-03 10:00:00';

        for ($pass = 0; $pass < 6; $pass++) {
            $bracket = $service->generateBracket($group->fresh(), $config);
            $created = false;

            foreach ($bracket as $round) {
                foreach ($round['matches'] as $match) {
                    $home = $match['home_team'] ?? null;
                    $away = $match['away_team'] ?? null;

                    if (!$home || !$away) {
                        continue;
                    }

                    $exists = Game::where('group_id', $group->id)
                        ->where(function ($query) use ($home, $away) {
                            $query->where(function ($q) use ($home, $away) {
                                $q->where('home_application_id', $home['application_id'])
                                    ->where('away_application_id', $away['application_id']);
                            })->orWhere(function ($q) use ($home, $away) {
                                $q->where('home_application_id', $away['application_id'])
                                    ->where('away_application_id', $home['application_id']);
                            });
                        })
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $winnerIsHome = $home['position'] <= $away['position'];
                    $this->createSeries(
                        $stage,
                        $group,
                        $home['application_id'],
                        $away['application_id'],
                        $winnerIsHome,
                        $matchFormat,
                        $time
                    );
                    $created = true;
                }
            }

            if (!$created) {
                break;
            }
        }
    }

    private function createSeries(
        TournamentStage $stage,
        StageGroup $group,
        int $homeApplicationId,
        int $awayApplicationId,
        bool $winnerIsHome,
        string $matchFormat,
        string &$time
    ): void {
        $winsNeeded = $matchFormat === 'best_of_3' ? 2 : 1;
        $gamesNeeded = $matchFormat === 'best_of_3' ? 3 : 1;

        $winnerWins = 0;

        for ($i = 0; $i < $gamesNeeded; $i++) {
            if ($winnerWins >= $winsNeeded) {
                break;
            }

            // В чётных играх серии хозяевами выступает другая команда.
            $swap = $matchFormat === 'best_of_3' && $i % 2 === 1;

            if ($swap) {
                $gameHomeId = $awayApplicationId;
                $gameAwayId = $homeApplicationId;
                $homeIsWinner = !$winnerIsHome;
            } else {
                $gameHomeId = $homeApplicationId;
                $gameAwayId = $awayApplicationId;
                $homeIsWinner = $winnerIsHome;
            }

            if ($homeIsWinner) {
                $homeScore = 3;
                $awayScore = rand(0, 2);
            } else {
                $homeScore = rand(0, 2);
                $awayScore = 3;
            }

            $winnerWins++;

            $game = Game::create([
                'stage_id' => $stage->id,
                'group_id' => $group->id,
                'home_application_id' => $gameHomeId,
                'away_application_id' => $gameAwayId,
                'venue_id' => $this->venue->id,
                'scheduled_time' => $time,
                'status' => 'completed',
                'home_score' => $homeScore,
                'away_score' => $awayScore,
            ]);

            $this->generateSets($game);
            $time = date('Y-m-d H:i:s', strtotime($time) + 3600);
        }
    }

    private function generateSets(Game $game): void
    {
        $homeScore = $game->home_score;
        $awayScore = $game->away_score;
        $totalSets = $homeScore + $awayScore;

        $homeSetsWon = 0;
        for ($i = 1; $i <= $totalSets; $i++) {
            $isLastSet = ($i === $totalSets);
            $target = $isLastSet ? 15 : 25;

            if ($homeSetsWon < $homeScore) {
                $homeSetScore = $target;
                $awaySetScore = rand($target - 10, $target - 1);
                $homeSetsWon++;
            } else {
                $awaySetScore = $target;
                $homeSetScore = rand($target - 10, $target - 1);
            }

            GameSet::create([
                'game_id' => $game->id,
                'set_number' => $i,
                'home_score' => $homeSetScore,
                'away_score' => $awaySetScore,
            ]);
        }
    }
}

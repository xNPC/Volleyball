<?php

namespace Database\Seeders;

use App\Models\ApplicationRoster;
use App\Models\Game;
use App\Models\GameSet;
use App\Models\PlayoffConfig;
use App\Models\StageGroup;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTournament1();
        $this->seedTournament2();
        $this->seedTournament3();
        $this->seedTournament4();
        $this->seedTournament5();
        $this->seedTournament6();
    }

    private function seedTournament1(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Кубок Весны-2026'],
            [
                'organization_id' => 1,
                'volleyball_type' => 'indoor',
                'description' => 'Весенний турнир по волейболу среди мужских команд.',
                'start_date' => '2026-03-10',
                'end_date' => '2026-03-15',
                'status' => 'completed',
                'rules' => 'Турнир проводится по системе плей-офф. Матчи играются до 3 побед.',
                'needs_venue' => true,
                'addition_deadline_male' => '2026-03-08',
                'addition_deadline_female' => '2026-03-08',
                'transfer_deadline_male' => '2026-03-12',
                'transfer_deadline_female' => '2026-03-12',
                'transfers_limit' => 2,
            ]
        );

        $teams = \App\Models\Team::where('name', '!=', 'Калиina')
            ->where('name', '!=', 'Берёзка')
            ->where('name', '!=', 'Лада')
            ->where('name', '!=', 'Уралочка')
            ->limit(8)
            ->get();

        $applications = $this->createApplicationsWithRosters($tournament, $teams);

        $stage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Групповой этап',
            'stage_type' => 'group',
            'order' => 1,
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-12',
        ]);

        $groupA = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа A',
            'order' => 1,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $groupB = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа B',
            'order' => 2,
            'team_count' => 4,
            'is_active' => true,
        ]);

        foreach ($applications->take(4) as $index => $app) {
            $groupA->teams()->attach($app->id, ['position' => $index + 1]);
        }

        foreach ($applications->slice(4) as $index => $app) {
            $groupB->teams()->attach($app->id, ['position' => $index + 1]);
        }

        $this->generateRoundRobinGames($groupA, $stage, 'indoor', '2026-03-10 10:00:00');
        $this->generateRoundRobinGames($groupB, $stage, 'indoor', '2026-03-10 14:00:00');

        $playoffStage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Плейофф',
            'stage_type' => 'playoff',
            'order' => 2,
            'start_date' => '2026-03-13',
            'end_date' => '2026-03-15',
        ]);

        $playoffGroup = StageGroup::create([
            'stage_id' => $playoffStage->id,
            'name' => 'Плейофф',
            'order' => 1,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $playoffTeams = $applications->take(4);
        foreach ($playoffTeams as $index => $app) {
            $playoffGroup->teams()->attach($app->id, ['position' => $index + 1]);
        }

        PlayoffConfig::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'total_teams' => 4,
            'bye_positions' => [],
            'reverse_seeding' => false,
            'match_format' => 'single',
        ]);

        $venue = Venue::first();
        $gameDate = Carbon::parse('2026-03-13 10:00:00');

        $sf1 = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[0]->id,
            'away_application_id' => $playoffTeams[3]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate,
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 1,
        ]);
        $this->generateSets($sf1, 'indoor');

        $sf2 = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[1]->id,
            'away_application_id' => $playoffTeams[2]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate->copy()->addHours(2),
            'status' => 'completed',
            'home_score' => 2,
            'away_score' => 3,
        ]);
        $this->generateSets($sf2, 'indoor');

        $final = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[0]->id,
            'away_application_id' => $playoffTeams[2]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate->copy()->addDay()->addHours(2),
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 2,
        ]);
        $this->generateSets($final, 'indoor');

        $thirdPlace = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[3]->id,
            'away_application_id' => $playoffTeams[1]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate->copy()->addDay(),
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 0,
        ]);
        $this->generateSets($thirdPlace, 'indoor');
    }

    private function seedTournament2(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Летняя лига-2026'],
            [
                'organization_id' => 1,
                'volleyball_type' => 'indoor',
                'description' => 'Летний турнир по волейболу среди мужских команд.',
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-20',
                'status' => 'ongoing',
                'rules' => 'Групповой этап с последующим плейофф.',
                'needs_venue' => true,
                'addition_deadline_male' => '2026-06-13',
                'addition_deadline_female' => '2026-06-13',
                'transfer_deadline_male' => '2026-06-18',
                'transfer_deadline_female' => '2026-06-18',
                'transfers_limit' => 3,
            ]
        );

        $teams = \App\Models\Team::where('name', '!=', 'Калиina')
            ->where('name', '!=', 'Берёзка')
            ->where('name', '!=', 'Лада')
            ->where('name', '!=', 'Уралочка')
            ->limit(12)
            ->get();

        $applications = $this->createApplicationsWithRosters($tournament, $teams);

        $stage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Групповой этап',
            'stage_type' => 'group',
            'order' => 1,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-18',
        ]);

        $groupA = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа A',
            'order' => 1,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $groupB = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа B',
            'order' => 2,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $groupC = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа C',
            'order' => 3,
            'team_count' => 4,
            'is_active' => true,
        ]);

        foreach ($applications->take(4) as $index => $app) {
            $groupA->teams()->attach($app->id, ['position' => $index + 1]);
        }
        foreach ($applications->slice(4, 4) as $index => $app) {
            $groupB->teams()->attach($app->id, ['position' => $index + 1]);
        }
        foreach ($applications->slice(8) as $index => $app) {
            $groupC->teams()->attach($app->id, ['position' => $index + 1]);
        }

        $this->generateRoundRobinGames($groupA, $stage, 'indoor', '2026-06-15 10:00:00', true);
        $this->generateRoundRobinGames($groupB, $stage, 'indoor', '2026-06-15 14:00:00', true);
        $this->generateRoundRobinGames($groupC, $stage, 'indoor', '2026-06-15 18:00:00', true);
    }

    private function seedTournament3(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Пляжный кубок-2026'],
            [
                'organization_id' => 2,
                'volleyball_type' => 'beach',
                'description' => 'Турнир по пляжному волейболу.',
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-05',
                'status' => 'ongoing',
                'rules' => 'Пляжный волейбол, матчи до 2 побед.',
                'needs_venue' => false,
            ]
        );

        $teams = \App\Models\Team::where('name', '!=', 'Калиina')
            ->where('name', '!=', 'Берёзка')
            ->where('name', '!=', 'Лада')
            ->where('name', '!=', 'Уралочка')
            ->limit(8)
            ->get();

        $applications = $this->createApplicationsWithRosters($tournament, $teams);

        $stage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Групповой этап',
            'stage_type' => 'group',
            'order' => 1,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-03',
        ]);

        $groupA = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа A',
            'order' => 1,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $groupB = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа B',
            'order' => 2,
            'team_count' => 4,
            'is_active' => true,
        ]);

        foreach ($applications->take(4) as $index => $app) {
            $groupA->teams()->attach($app->id, ['position' => $index + 1]);
        }
        foreach ($applications->slice(4) as $index => $app) {
            $groupB->teams()->attach($app->id, ['position' => $index + 1]);
        }

        $this->generateRoundRobinGames($groupA, $stage, 'beach', '2026-07-01 10:00:00', true);
        $this->generateRoundRobinGames($groupB, $stage, 'beach', '2026-07-01 14:00:00', true);
    }

    private function seedTournament4(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Кубок Осени-2026'],
            [
                'organization_id' => 1,
                'volleyball_type' => 'indoor',
                'description' => 'Осенний турнир по волейболу.',
                'start_date' => '2026-10-20',
                'end_date' => '2026-10-25',
                'status' => 'planned',
                'rules' => 'Групповой этап с последующим плейофф.',
                'needs_venue' => true,
                'addition_deadline_male' => '2026-10-18',
                'addition_deadline_female' => '2026-10-18',
                'transfer_deadline_male' => '2026-10-23',
                'transfer_deadline_female' => '2026-10-23',
                'transfers_limit' => 2,
            ]
        );

        $teams = \App\Models\Team::where('name', '!=', 'Калиina')
            ->where('name', '!=', 'Берёзка')
            ->where('name', '!=', 'Лада')
            ->where('name', '!=', 'Уралочка')
            ->limit(10)
            ->get();

        foreach ($teams->take(6) as $team) {
            $this->createApplication($tournament, $team, 'approved', 1);
        }

        foreach ($teams->slice(6, 2) as $team) {
            $this->createApplication($tournament, $team, 'pending', 0);
        }

        foreach ($teams->slice(8, 2) as $team) {
            $this->createApplication($tournament, $team, 'rejected', 1);
        }
    }

    private function seedTournament5(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Женский кубок-2026'],
            [
                'organization_id' => 1,
                'volleyball_type' => 'indoor',
                'description' => 'Турнир по волейболу среди женских команд.',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-15',
                'status' => 'planned',
                'rules' => 'Групповой этап с последующим плейофф.',
                'needs_venue' => false,
            ]
        );

        $teams = \App\Models\Team::whereIn('name', ['Калиina', 'Берёзка', 'Лада', 'Уралочка'])->get();

        foreach ($teams->take(4) as $team) {
            $this->createApplication($tournament, $team, 'approved', 1);
        }

        foreach ($teams->slice(4) as $team) {
            $this->createApplication($tournament, $team, 'pending', 0);
        }
    }

    private function seedTournament6(): void
    {
        $tournament = Tournament::updateOrCreate(
            ['name' => 'Зимний кубок-2026'],
            [
                'organization_id' => 2,
                'volleyball_type' => 'indoor',
                'description' => 'Зимний турнир по волейболу.',
                'start_date' => '2026-01-15',
                'end_date' => '2026-01-20',
                'status' => 'completed',
                'rules' => 'Групповой этап с последующим плейофф.',
                'needs_venue' => true,
            ]
        );

        $teams = \App\Models\Team::where('name', '!=', 'Калиina')
            ->where('name', '!=', 'Берёзка')
            ->where('name', '!=', 'Лада')
            ->where('name', '!=', 'Уралочка')
            ->limit(6)
            ->get();

        $applications = $this->createApplicationsWithRosters($tournament, $teams);

        $stage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Групповой этап',
            'stage_type' => 'group',
            'order' => 1,
            'start_date' => '2026-01-15',
            'end_date' => '2026-01-17',
        ]);

        $groupA = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа A',
            'order' => 1,
            'team_count' => 3,
            'is_active' => true,
        ]);

        $groupB = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Группа B',
            'order' => 2,
            'team_count' => 3,
            'is_active' => true,
        ]);

        foreach ($applications->take(3) as $index => $app) {
            $groupA->teams()->attach($app->id, ['position' => $index + 1]);
        }
        foreach ($applications->slice(3) as $index => $app) {
            $groupB->teams()->attach($app->id, ['position' => $index + 1]);
        }

        $this->generateRoundRobinGames($groupA, $stage, 'indoor', '2026-01-15 10:00:00');
        $this->generateRoundRobinGames($groupB, $stage, 'indoor', '2026-01-15 14:00:00');

        $playoffStage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Плейофф',
            'stage_type' => 'playoff',
            'order' => 2,
            'start_date' => '2026-01-18',
            'end_date' => '2026-01-20',
        ]);

        $playoffGroup = StageGroup::create([
            'stage_id' => $playoffStage->id,
            'name' => 'Плейофф',
            'order' => 1,
            'team_count' => 4,
            'is_active' => true,
        ]);

        $playoffTeams = $applications->take(4);
        foreach ($playoffTeams as $index => $app) {
            $playoffGroup->teams()->attach($app->id, ['position' => $index + 1]);
        }

        PlayoffConfig::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'total_teams' => 4,
            'bye_positions' => [],
            'reverse_seeding' => false,
            'match_format' => 'single',
        ]);

        $venue = Venue::first();
        $gameDate = Carbon::parse('2026-01-18 10:00:00');

        $sf1 = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[0]->id,
            'away_application_id' => $playoffTeams[3]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate,
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 0,
        ]);
        $this->generateSets($sf1, 'indoor');

        $sf2 = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[1]->id,
            'away_application_id' => $playoffTeams[2]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate->copy()->addHours(2),
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 2,
        ]);
        $this->generateSets($sf2, 'indoor');

        $final = Game::create([
            'stage_id' => $playoffStage->id,
            'group_id' => $playoffGroup->id,
            'home_application_id' => $playoffTeams[0]->id,
            'away_application_id' => $playoffTeams[1]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => $gameDate->copy()->addDay()->addHours(2),
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 1,
        ]);
        $this->generateSets($final, 'indoor');
    }

    private function createApplicationsWithRosters(Tournament $tournament, $teams)
    {
        $applications = collect();
        $positions = ['outside', 'setter', 'middle', 'opposite', 'libero'];

        $gender = $teams->first()->captain->gender;
        $availablePlayers = User::where('gender', $gender)
            ->where('email', '!=', 'admin@volleyball.local')
            ->get();

        $teamCount = $teams->count();
        $availableCount = $availablePlayers->count();
        $rosterSize = min(7, max(5, intdiv($availableCount, $teamCount)));

        $captains = $teams->pluck('captain_id');
        $pool = $availablePlayers->shuffle();

        $reservedCaptains = $pool->whereIn('id', $captains)->values();
        $pool = $pool->whereNotIn('id', $captains)->values();

        foreach ($teams as $team) {
            $application = $this->createApplication($tournament, $team, 'approved', 1);
            $applications->push($application);

            $roster = collect();

            if ($team->captain_id) {
                $captain = $reservedCaptains->firstWhere('id', $team->captain_id);
                if ($captain) {
                    $roster->push($captain);
                }
            }

            $needed = $rosterSize - $roster->count();
            for ($i = 0; $i < $needed && $pool->count() > 0; $i++) {
                $player = $pool->pop();
                $roster->push($player);
            }

            $jerseyNumbers = range(1, $roster->count());
            shuffle($jerseyNumbers);

            foreach ($roster as $index => $player) {
                ApplicationRoster::create([
                    'application_id' => $application->id,
                    'user_id' => $player->id,
                    'jersey_number' => $jerseyNumbers[$index],
                    'position' => $positions[array_rand($positions)],
                    'is_captain' => $player->id === $team->captain_id,
                ]);
            }
        }

        return $applications;
    }

    private function createApplication(Tournament $tournament, \App\Models\Team $team, string $status, int $isComplete): TournamentApplication
    {
        $venue = Venue::inRandomOrder()->first();

        return TournamentApplication::create([
            'tournament_id' => $tournament->id,
            'team_id' => $team->id,
            'venue_id' => $tournament->needs_venue ? $venue?->id : null,
            'status' => $status,
            'is_complete' => $isComplete,
        ]);
    }

    private function generateRoundRobinGames(StageGroup $group, TournamentStage $stage, string $type, string $startDate, bool $mixed = false): void
    {
        $applications = $group->teams()->get();
        $venue = Venue::inRandomOrder()->first();
        $currentDate = Carbon::parse($startDate);
        $gameIndex = 0;

        for ($i = 0; $i < $applications->count(); $i++) {
            for ($j = $i + 1; $j < $applications->count(); $j++) {
                $homeApp = $applications[$i];
                $awayApp = $applications[$j];

                $status = 'completed';
                $homeScore = null;
                $awayScore = null;

                if ($mixed) {
                    if ($gameIndex < 3) {
                        $status = 'completed';
                        [$homeScore, $awayScore] = $type === 'beach'
                            ? $this->generateBeachScore()
                            : $this->generateIndoorScore();
                    } elseif ($gameIndex === 3) {
                        $status = 'live';
                        $homeScore = 1;
                        $awayScore = 1;
                        $currentDate = now()->subMinutes(30);
                    } else {
                        $status = 'scheduled';
                        $currentDate = now()->addDays(rand(1, 3))->setTime(rand(10, 18), 0);
                    }
                } else {
                    [$homeScore, $awayScore] = $type === 'beach'
                        ? $this->generateBeachScore()
                        : $this->generateIndoorScore();
                }

                $game = Game::create([
                    'stage_id' => $stage->id,
                    'group_id' => $group->id,
                    'home_application_id' => $homeApp->id,
                    'away_application_id' => $awayApp->id,
                    'venue_id' => $venue->id,
                    'scheduled_time' => $currentDate,
                    'status' => $status,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                ]);

                if ($status === 'completed') {
                    $this->generateSets($game, $type);
                }

                $currentDate->addHours(3);
                $gameIndex++;
            }
        }
    }

    private function generateIndoorScore(): array
    {
        $outcomes = [[3, 0], [3, 1], [3, 2], [0, 3], [1, 3], [2, 3]];
        return $outcomes[array_rand($outcomes)];
    }

    private function generateBeachScore(): array
    {
        $outcomes = [[2, 0], [2, 1], [0, 2], [1, 2]];
        return $outcomes[array_rand($outcomes)];
    }

    private function generateSets(Game $game, string $type): void
    {
        $homeScore = $game->home_score;
        $awayScore = $game->away_score;
        $totalSets = $homeScore + $awayScore;
        $isBeach = $type === 'beach';

        $homeSetsWon = 0;
        $awaySetsWon = 0;

        for ($i = 1; $i <= $totalSets; $i++) {
            $isLastSet = ($i === $totalSets);
            $target = $isLastSet ? 15 : ($isBeach ? 21 : 25);

            if ($homeSetsWon < $homeScore) {
                $homeSetScore = $target;
                $awaySetScore = rand($target - 10, $target - 1);
                $homeSetsWon++;
            } else {
                $awaySetScore = $target;
                $homeSetScore = rand($target - 10, $target - 1);
                $awaySetsWon++;
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

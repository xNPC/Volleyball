<?php

namespace Database\Seeders;

use App\Models\Broadcast;
use App\Models\RosterRequest;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRosterRequests();
        $this->seedBroadcasts();
    }

    private function seedRosterRequests(): void
    {
        $tournament = Tournament::where('name', 'Летняя лига-2026')->first();
        if (! $tournament) {
            return;
        }

        $application = TournamentApplication::where('tournament_id', $tournament->id)
            ->where('status', 'approved')
            ->first();

        $player = User::where('gender', 'male')
            ->where('email', '!=', 'admin@volleyball.local')
            ->whereDoesntHave('applicationRosters', function ($q) use ($tournament) {
                $q->whereHas('application', fn ($q2) => $q2->where('tournament_id', $tournament->id));
            })
            ->first();

        if ($application && $player) {
            RosterRequest::updateOrCreate(
                [
                    'type' => 'addition',
                    'tournament_id' => $tournament->id,
                    'player_user_id' => $player->id,
                    'to_application_id' => $application->id,
                    'status' => 'pending',
                ],
                [
                    'jersey_number' => 15,
                    'position' => 'outside',
                    'comment' => 'Прошу добавить в состав',
                    'created_by' => $application->team->captain_id,
                ]
            );
        }

        $approvedApps = TournamentApplication::where('tournament_id', $tournament->id)
            ->where('status', 'approved')
            ->limit(2)
            ->get();

        if ($approvedApps->count() === 2) {
            $player2 = User::where('gender', 'male')
                ->where('email', '!=', 'admin@volleyball.local')
                ->whereHas('applicationRosters', function ($q) use ($tournament) {
                    $q->whereHas('application', fn ($q2) => $q2->where('tournament_id', $tournament->id));
                })
                ->first();

            if ($player2) {
                RosterRequest::updateOrCreate(
                    [
                        'type' => 'transfer',
                        'tournament_id' => $tournament->id,
                        'player_user_id' => $player2->id,
                        'to_application_id' => $approvedApps[0]->id,
                        'from_application_id' => $approvedApps[1]->id,
                        'status' => 'approved',
                    ],
                    [
                        'jersey_number' => 7,
                        'position' => 'setter',
                        'comment' => 'Переход из другой команды',
                        'created_by' => $approvedApps[0]->team->captain_id,
                        'reviewed_by' => 1,
                        'reviewed_at' => now(),
                    ]
                );
            }
        }
    }

    private function seedBroadcasts(): void
    {
        $tournament = Tournament::where('name', 'Летняя лига-2026')->first();
        if (! $tournament) {
            return;
        }

        $upcomingGame = $tournament->stages()
            ->whereHas('groups.games', function ($q) {
                $q->where('status', 'scheduled')
                    ->where('scheduled_time', '>', now());
            })
            ->first()
            ?->groups()
            ->whereHas('games', function ($q) {
                $q->where('status', 'scheduled')
                    ->where('scheduled_time', '>', now());
            })
            ->first()
            ?->games()
            ->where('status', 'scheduled')
            ->where('scheduled_time', '>', now())
            ->first();

        if ($upcomingGame) {
            Broadcast::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'game_id' => $upcomingGame->id,
                    'status' => 'scheduled',
                ],
                [
                    'title' => 'Трансляция: ' . $upcomingGame->homeApplication->team->name . ' — ' . $upcomingGame->awayApplication->team->name,
                    'description' => 'Прямая трансляция матча',
                    'scheduled_at' => $upcomingGame->scheduled_time,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }

        $liveGame = $tournament->stages()
            ->whereHas('groups.games', function ($q) {
                $q->where('status', 'live');
            })
            ->first()
            ?->groups()
            ->whereHas('games', function ($q) {
                $q->where('status', 'live');
            })
            ->first()
            ?->games()
            ->where('status', 'live')
            ->first();

        if ($liveGame) {
            Broadcast::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'game_id' => $liveGame->id,
                    'status' => 'live',
                ],
                [
                    'title' => 'LIVE: ' . $liveGame->homeApplication->team->name . ' — ' . $liveGame->awayApplication->team->name,
                    'description' => 'Матч идёт в прямом эфире',
                    'started_at' => now()->subMinutes(30),
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );
        }

        $completedTournament = Tournament::where('name', 'Кубок Весны-2026')->first();
        if ($completedTournament) {
            $completedGame = $completedTournament->stages()
                ->whereHas('groups.games', function ($q) {
                    $q->where('status', 'completed');
                })
                ->first()
                ?->groups()
                ->whereHas('games', function ($q) {
                    $q->where('status', 'completed');
                })
                ->first()
                ?->games()
                ->where('status', 'completed')
                ->first();

            if ($completedGame) {
                Broadcast::updateOrCreate(
                    [
                        'tournament_id' => $completedTournament->id,
                        'game_id' => $completedGame->id,
                        'status' => 'finished',
                    ],
                    [
                        'title' => 'Запись: ' . $completedGame->homeApplication->team->name . ' — ' . $completedGame->awayApplication->team->name,
                        'description' => 'Запись завершённого матча',
                        'vk_url' => 'https://vk.com/video-123456789_456239017',
                        'ended_at' => now()->subDays(30),
                        'is_active' => true,
                        'sort_order' => 3,
                    ]
                );
            }
        }
    }
}

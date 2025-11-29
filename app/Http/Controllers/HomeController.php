<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Team;
use App\Models\TournamentApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Ближайшие турниры
        $featuredTournaments = Tournament::with(['approvedApplications.team'])
            ->where('start_date', '>=', now())
            ->where('status', 'planned')
            ->orderBy('start_date')
            ->limit(3)
            ->get()
            ->map(function($tournament) {
                $teamsCount = $tournament->approvedApplications->count();

                return [
                    'name' => $tournament->name,
                    'teams_count' => $teamsCount,
                    'location' => $tournament->location ?? 'Не указано',
                    'date' => $tournament->start_date ? $tournament->start_date->format('d.m.Y') : 'Дата не указана',
                    'prize' => 'Не указан',
                ];
            });

        // Ближайшие матчи
        $upcomingMatches = Game::with([
            'homeApplication.team',
            'awayApplication.team',
            'stage.tournament',
            'venue'
        ])
            ->where('scheduled_time', '>=', now())
            ->whereNotNull('scheduled_time')
            ->orderBy('scheduled_time')
            ->limit(10)
            ->get()
            ->map(function($game) {
                return [
                    'team1_name' => $game->homeApplication->team->name ?? 'TBA',
                    'team2_name' => $game->awayApplication->team->name ?? 'TBA',
                    'date' => $game->scheduled_time->format('d.m.Y'),
                    'time' => $game->scheduled_time->format('H:i'),
                    'location' => $game->venue ? ($game->venue->name . ($game->venue->address ? ', ' . $game->venue->address : '')) : 'Не указано',
                    'tournament' => $game->stage->tournament->name ?? 'Турнир',
                ];
            });

        // Прошедшие матчи
        $pastMatches = Game::with([
            'homeApplication.team',
            'awayApplication.team',
            'stage.tournament',
            'sets',
            'venue'
        ])
            ->where('scheduled_time', '<', now())
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->orderBy('scheduled_time', 'desc')
            ->limit(10)
            ->get()
            ->map(function($game) {
                $winner = $game->home_score > $game->away_score ? 'team1' : 'team2';

                $setsString = $game->sets->map(function($set) {
                    return $set->home_score . ':' . $set->away_score;
                })->implode(', ');

                return [
                    'team1_name' => $game->homeApplication->team->name ?? 'TBA',
                    'team2_name' => $game->awayApplication->team->name ?? 'TBA',
                    'score' => $game->home_score . ':' . $game->away_score,
                    'date' => $game->scheduled_time->format('d.m.Y'),
                    'time' => $game->scheduled_time->format('H:i'),
                    'sets' => $setsString ? 'Сеты: ' . $setsString : 'Сеты не указаны',
                    'winner' => $winner,
                    'location' => $game->venue ? ($game->venue->name . ($game->venue->address ? ', ' . $game->venue->address : '')) : 'Не указано',
                    'tournament' => $game->stage->tournament->name ?? 'Турнир',
                ];
            });

        // Лучшие команды (по реальным победам в играх)
        $topTeams = Team::withCount([
            'gamesAsHome as home_wins' => function($query) {
                $query->where('games.status', 'completed')
                    ->whereColumn('games.home_score', '>', 'games.away_score');
            },
            'gamesAsAway as away_wins' => function($query) {
                $query->where('games.status', 'completed')
                    ->whereColumn('games.away_score', '>', 'games.home_score');
            }
        ])
            ->withCount([
                'gamesAsHome as home_games' => function($query) {
                    $query->where('games.status', 'completed');
                },
                'gamesAsAway as away_games' => function($query) {
                    $query->where('games.status', 'completed');
                }
            ])
            ->get()
            ->map(function($team) {
                $totalWins = $team->home_wins + $team->away_wins;
                $totalGames = $team->home_games + $team->away_games;

                return [
                    'team' => $team,
                    'name' => $team->name,
                    'logo' => substr($team->name, 0, 2),
                    'wins' => $totalWins,
                    'total_games' => $totalGames,
                    'win_rate' => $totalGames > 0 ? round(($totalWins / $totalGames) * 100, 1) : 0
                ];
            })
            ->filter(function($team) {
                return $team['total_games'] > 0; // показываем только команды, которые играли
            })
            ->sortByDesc('wins')
            ->take(4)
            ->values();

        // Пользователи с днем рождения сегодня
        $birthdayUsers = User::whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day)
            ->whereNotNull('birthday')
            ->limit(8)
            ->get()
            ->map(function($user) {
                $birthday = Carbon::parse($user->birthday);
                $user->age = $birthday->diffInYears(now());
                $position = $user->applicationRosters()->first();
                $user->position = $position ? $position->position : 'Игрок';
                return $user;
            });

        return view('home-page', compact(
            'featuredTournaments',
            'upcomingMatches',
            'pastMatches',
            'topTeams',
            'birthdayUsers'
        ));
    }
}

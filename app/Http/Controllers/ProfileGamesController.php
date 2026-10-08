<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class ProfileGamesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applicationIds = $user->applicationRosters()->pluck('application_id');

        $upcomingMatches = Game::where(function ($query) use ($applicationIds) {
                $query->whereIn('home_application_id', $applicationIds)
                    ->orWhereIn('away_application_id', $applicationIds);
            })
            ->with(['homeApplication.team', 'awayApplication.team', 'stage.tournament', 'venue'])
            ->whereNotNull('scheduled_time')
            ->where('scheduled_time', '>=', now())
            ->orderBy('scheduled_time')
            ->limit(50)
            ->get()
            ->map(function ($game) {
                return [
                    'team1_name' => $game->homeApplication->team->name ?? 'TBA',
                    'team2_name' => $game->awayApplication->team->name ?? 'TBA',
                    'date' => $game->scheduled_time->format('d.m.Y'),
                    'time' => $game->scheduled_time->format('H:i'),
                    'location' => $game->venue ? ($game->venue->name . ($game->venue->address ? ', ' . $game->venue->address : '')) : 'Не указано',
                    'tournament' => $game->stage->tournament->name ?? 'Турнир',
                ];
            });

        $pastMatches = Game::where(function ($query) use ($applicationIds) {
                $query->whereIn('home_application_id', $applicationIds)
                    ->orWhereIn('away_application_id', $applicationIds);
            })
            ->with(['homeApplication.team', 'awayApplication.team', 'stage.tournament', 'sets', 'venue'])
            ->where('scheduled_time', '<', now())
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->orderBy('scheduled_time', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($game) {
                $winner = $game->home_score > $game->away_score ? 'team1' : 'team2';

                $setsString = $game->sets->map(function ($set) {
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

        return view('profile.games', compact('upcomingMatches', 'pastMatches'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TournamentStage;
use App\Models\Tournament;
use App\Services\GroupStandingsService;
use Illuminate\Http\Request;

class StageController extends Controller
{
    private GroupStandingsService $standingsService;

    public function __construct(GroupStandingsService $standingsService)
    {
        $this->standingsService = $standingsService;
    }

    public function show(Tournament $tournament, TournamentStage $stage)
    {
        // Загружаем группы с играми, командами и сетами
        $stage->load([
            'groups.teams.team',
            'groups.games' => function($query) {
                $query->with(['sets', 'homeApplication.team', 'awayApplication.team'])->orderBy('scheduled_time', 'asc');
            }
        ]);

        // Рассчитываем статистику для каждой группы
        $groupsWithStandings = $stage->groups->map(function ($group) {
            $group->standings = $this->standingsService->calculateStandings($group);
            return $group;
        });

        return view('stages.show', compact('tournament', 'stage', 'groupsWithStandings'));
    }

    private function getGameDetailsFromCollection($games, $team1Id, $team2Id)
    {
        $game = $games->first(function($game) use ($team1Id, $team2Id) {
            return ($game->home_application_id == $team1Id && $game->away_application_id == $team2Id) ||
                ($game->home_application_id == $team2Id && $game->away_application_id == $team1Id);
        });

        if (!$game || $game->sets->isEmpty()) {
            return null;
        }

        $setsDetails = [];
        foreach ($game->sets as $set) {
            $setsDetails[] = "{$set->home_score}:{$set->away_score}";
        }

        return "Сеты: " . implode(', ', $setsDetails);
    }

}

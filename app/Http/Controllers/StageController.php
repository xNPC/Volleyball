<?php

namespace App\Http\Controllers;

use App\Models\TournamentStage;
use App\Models\Tournament;
use App\Services\GroupStandingsService;
use App\Services\PlayoffBracketGenerator;
use App\Services\PlayoffConfigurator;
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
        // Загружаем группы с командами и играми
        $stage->load([
            'groups' => function($query) {
                $query->orderBy('order');
            },
            'groups.teams.team',  // Это загружает команды через заявки
            'groups.games' => function($query) {
                $query->with(['sets', 'homeApplication.team', 'awayApplication.team']);
            },
            'playoffConfig'
        ]);

        if ($stage->stage_type === 'group') {
            // Для группового этапа - рассчитываем статистику
            $groupsWithStandings = $this->standingsService->calculateStandingsForStage($stage);
            return view('stages.show', compact('tournament', 'stage', 'groupsWithStandings'));

        } else {
            // Для плейофф - генерируем сетку для каждой группы
            $groupsWithBrackets = collect();

            foreach ($stage->groups as $group) {
                // Собираем команды с их позициями
                $teams = collect();
                foreach ($group->teams as $application) {
                    if ($application->team) {
                        $team = $application->team;
                        $team->position = $application->pivot->position ?? null;
                        $teams->push($team);
                    }
                }

                // Сортируем по позиции
                $teams = $teams->sortBy('position')->values();

                // Генерируем сетку для группы
                $bracketGenerator = app(PlayoffBracketGenerator::class);
                $bracket = $bracketGenerator->generateBracket($stage, $teams, $group->id);

                $group->bracket = $bracket;
                $groupsWithBrackets->push($group);
            }

            return view('stages.playoff', compact('tournament', 'stage', 'groupsWithBrackets'));
        }
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

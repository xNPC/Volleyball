<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Team;
use Illuminate\Http\Request;

class TournamentTeamController extends Controller
{
    public function roster(Tournament $tournament, Team $team, Request $request)
    {
        // Проверяем, что команда участвует в турнире
        $application = $tournament->tournamentApplications()
            ->where('team_id', $team->id)
            ->where('status', 'approved')
            ->firstOrFail();

        // Получаем состав команды для этой заявки
        $roster = $application->roster()
            ->with(['user'])
            //->orderBy('user.name')
            ->get();

        // Группируем по ролям для красивого отображения
        $groupedRoster = $roster->groupBy('jersey_number');

        return view('tournaments.teams.roster', compact('tournament', 'team', 'application', 'roster', 'groupedRoster'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Team;

class TeamController extends Controller
{
    public function show(Team $team)
    {
        $activeTournamentStatuses = ['ongoing', 'planned'];

        // Загружаем данные команды
        $team->load([
            'captain',
            // В блок активных турниров загружаем только те, которые идут или запланированы
            'activeTournaments' => function($query) use ($activeTournamentStatuses) {
                $query->whereNull('tournament_applications.deleted_at')
                    ->whereIn('tournaments.status', $activeTournamentStatuses);
            },
            // В историю (tournamentApplications) загружаем всё подряд, включая завершенные турниры
            'tournamentApplications' => function($query) {
                $query->with(['tournament', 'roster.user'])
                    ->where('status', 'approved')
                    ->whereNull('tournament_applications.deleted_at')
                    ->orderBy('created_at', 'desc');
            },
            'tournamentApplications.roster.user'
        ]);

        return view('teams.show', compact('team'));
    }
}

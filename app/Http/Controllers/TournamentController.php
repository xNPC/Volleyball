<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::withCount('stages')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament)
    {
        $tournament->load(['stages' => function($query) {
            $query->orderBy('order');
        }]);

        return view('tournaments.show', compact('tournament'));
    }

    public function teams(Tournament $tournament, Request $request)
    {
        $search = $request->input('search');

        // Получаем команды, которые подали заявки на этот турнир
        $teams = $tournament->teams()
            ->withCount([
                'activeTournaments as tournaments_count',
                'tournamentApplications as applications_count'
            ])
            ->with(['captain'])
            ->wherePivot('status', 'approved') // Только принятые заявки
            ->when($search, function($query) use ($search) {
                $query->where('teams.name', 'like', "%{$search}%")
                    ->orWhere('teams.city', 'like', "%{$search}%")
                    ->orWhere('teams.description', 'like', "%{$search}%");
            })
            ->orderBy('teams.name')
            ->paginate(12)
            ->withQueryString();

        // Статистика для заголовка
        $stats = [
            'total_teams' => $tournament->teams()->wherePivot('status', 'approved')->count(),
            'pending_applications' => $tournament->tournamentApplications()->where('status', 'pending')->count(),
        ];

        return view('tournaments.teams', compact('tournament', 'teams', 'search', 'stats'));
    }
}

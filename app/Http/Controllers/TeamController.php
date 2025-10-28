<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');

        $teams = Team::withCount([
            'tournamentApplications as applications_count',
            'activeTournaments as active_tournaments_count'
        ])
            ->with(['captain'])
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($filter === 'with_tournaments', function($query) {
                $query->has('activeTournaments');
            })
            ->when($filter === 'new', function($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        // Статистика для заголовка
        $stats = [
            'total_teams' => Team::count(),
            'new_teams_month' => Team::where('created_at', '>=', now()->subMonth())->count(),
            'teams_with_tournaments' => Team::has('activeTournaments')->count(),
        ];

        return view('teams.index', compact('teams', 'search', 'filter', 'stats'));
    }

    public function show(Team $team)
    {
        // Загружаем данные команды
        $team->load([
            'captain',
            'activeTournaments',
            'tournamentApplications' => function($query) {
                $query->with(['tournament', 'roster.user'])
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc');
            },
            'tournamentApplications.roster.user'
        ]);

        return view('teams.show', compact('team'));
    }
}

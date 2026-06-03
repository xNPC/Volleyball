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

        // Массив статусов, которые мы считаем "активными" для турнира
        $activeTournamentStatuses = ['ongoing', 'planned'];

        $teams = Team::withCount([
            // Считаем вообще все неудаленные заявки команды (для истории/общей статистики)
            'tournamentApplications as applications_count' => function ($query) {
                $query->whereNull('tournament_applications.deleted_at');
            },
            // Считаем ТОЛЬКО активные турниры (заявка не удалена + статус турнира активный)
            'activeTournaments as active_tournaments_count' => function ($query) use ($activeTournamentStatuses) {
                $query->whereNull('tournament_applications.deleted_at')
                    ->whereIn('tournaments.status', $activeTournamentStatuses);
            }
        ])
            ->with(['captain'])
            ->when($search, function($query) use ($search) {
                $query->where(function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'with_tournaments', function($query) use ($activeTournamentStatuses) {
                // Показываем команды, у которых есть живые заявки в именно АКТИВНЫХ турнирах
                $query->whereHas('activeTournaments', function($q) use ($activeTournamentStatuses) {
                    $q->whereNull('tournament_applications.deleted_at')
                        ->whereIn('tournaments.status', $activeTournamentStatuses);
                });
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
            'teams_with_tournaments' => Team::whereHas('activeTournaments', function($q) use ($activeTournamentStatuses) {
                $q->whereNull('tournament_applications.deleted_at')
                    ->whereIn('tournaments.status', $activeTournamentStatuses);
            })->count(),
        ];

        return view('teams.index', compact('teams', 'search', 'filter', 'stats'));
    }

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

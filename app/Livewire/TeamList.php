<?php

namespace App\Livewire;

use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TeamList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $filter = 'all';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter']);
        $this->resetPage();
    }

    public function render()
    {
        $activeTournamentStatuses = ['ongoing', 'planned'];

        $teams = Team::withCount([
            'tournamentApplications as applications_count' => function ($query) use ($activeTournamentStatuses) {
                $query->whereNull('tournament_applications.deleted_at');
            },
            'activeTournaments as active_tournaments_count' => function ($query) use ($activeTournamentStatuses) {
                $query->whereNull('tournament_applications.deleted_at')
                    ->whereIn('tournaments.status', $activeTournamentStatuses);
            }
        ])
            ->with(['captain'])
            ->when($this->search, function($query) {
                $query->where(function($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filter === 'with_tournaments', function($query) use ($activeTournamentStatuses) {
                $query->whereHas('activeTournaments', function($q) use ($activeTournamentStatuses) {
                    $q->whereNull('tournament_applications.deleted_at')
                        ->whereIn('tournaments.status', $activeTournamentStatuses);
                });
            })
            ->when($this->filter === 'new', function($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.team-list', [
            'teams' => $teams,
            'stats' => [
                'total_teams' => Team::count(),
                'new_teams_month' => Team::where('created_at', '>=', now()->subMonth())->count(),
                'teams_with_tournaments' => Team::whereHas('activeTournaments', function($q) use ($activeTournamentStatuses) {
                    $q->whereNull('tournament_applications.deleted_at')
                        ->whereIn('tournaments.status', $activeTournamentStatuses);
                })->count(),
            ],
        ]);
    }
}
<?php

namespace App\Livewire;

use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TournamentTeamList extends Component
{
    use WithPagination;

    public Tournament $tournament;

    #[Url]
    public $search = '';

    public function mount(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render()
    {
        $teams = $this->tournament->teams()
            ->withCount([
                'activeTournaments as tournaments_count',
                'tournamentApplications as applications_count'
            ])
            ->with(['captain'])
            ->wherePivot('status', 'approved')
            ->when($this->search, function($query) {
                $query->where('teams.name', 'like', "%{$this->search}%")
                    ->orWhere('teams.description', 'like', "%{$this->search}%");
            })
            ->orderBy('teams.name')
            ->paginate(12);

        return view('livewire.tournament-team-list', [
            'teams' => $teams,
            'stats' => [
                'total_teams' => $this->tournament->teams()->wherePivot('status', 'approved')->count(),
                'pending_applications' => $this->tournament->tournamentApplications()->where('status', 'pending')->count(),
            ],
        ]);
    }
}
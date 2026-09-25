<?php

namespace App\Livewire;

use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TournamentList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $status = 'all';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $tournaments = Tournament::withCount(['stages', 'approvedTeams as teams_count'])
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->when($this->status !== 'all', function($query) {
                $query->where('status', $this->status);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(9);

        return view('livewire.tournament-list', [
            'tournaments' => $tournaments,
        ]);
    }
}
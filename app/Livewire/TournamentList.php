<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tournament;

class TournamentList extends Component
{
    public $selectedTournament = null;

    protected $listeners = ['backToTournaments' => 'backToList'];

    public function selectTournament($tournamentId)
    {
        $this->selectedTournament = $tournamentId;
    }

    public function backToList()
    {
        $this->selectedTournament = null;
    }

    public function render()
    {
        return view('livewire.tournament-list', [
            'tournaments' => Tournament::withCount('stages')->get()
        ]);
    }
}

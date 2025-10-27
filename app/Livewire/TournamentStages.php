<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tournament;
use App\Models\TournamentStage as Stage;

class TournamentStages extends Component
{
    public $tournamentId;
    public $selectedStage = null;

    protected $listeners = ['backToStages' => 'backToStagesHandler'];

    public function mount($tournamentId)
    {
        $this->tournamentId = $tournamentId;
    }

    public function selectStage($stageId)
    {
        $this->selectedStage = $stageId;
    }

    public function backToStagesHandler()
    {
        $this->selectedStage = null;
    }

    public function backToTournaments()
    {
        $this->emit('backToTournaments');
    }

    public function render()
    {
        $tournament = Tournament::with('stages')->findOrFail($this->tournamentId);

        return view('livewire.tournament-stages', [
            'tournament' => $tournament,
            'stages' => $tournament->stages
        ]);
    }
}

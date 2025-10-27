<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TournamentStage as Stage;
use App\Models\StageGroup as Group;

class StageGroups extends Component
{
    public $stageId;
    public $selectedGroup = null;

    protected $listeners = ['backToGroups' => 'backToGroupsHandler'];

    public function mount($stageId)
    {
        $this->stageId = $stageId;
    }

    public function selectGroup($groupId)
    {
        $this->selectedGroup = $groupId;
    }

    public function backToGroupsHandler()
    {
        $this->selectedGroup = null;
    }

    public function backToStages()
    {
        $this->emit('backToStages');
    }

    public function render()
    {
        $stage = Stage::with(['groups.teams' => function($query) {
            $query->orderBy('points', 'desc');
        }])->findOrFail($this->stageId);

        return view('livewire.stage-groups', [
            'stage' => $stage,
            'groups' => $stage->groups
        ]);
    }
}

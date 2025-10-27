<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StageGroup as Group;

class GroupTeams extends Component
{
    public $groupId;

    protected $listeners = ['backToGroups' => 'backToGroupsHandler'];

    public function mount($groupId)
    {
        $this->groupId = $groupId;
    }

    public function backToGroupsHandler()
    {
        $this->emit('backToGroups');
    }

    public function render()
    {
        $group = Group::with(['teams' => function($query) {
            $query->orderBy('points', 'desc');
        }])->findOrFail($this->groupId);

        return view('livewire.group-teams', [
            'group' => $group,
            'teams' => $group->teams
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StageGroup as Group;
use App\Services\GroupStandingsService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(private GroupStandingsService $standingsService)
    {
    }

    public function show(Group $group)
    {
        $group->load(['stage.tournament', 'teams' => function($query) {
            //$query->orderBy('name', 'desc');
        }]);

        $group->standings = $this->standingsService->calculateStandings($group);

        return view('groups.show', compact('group'));
    }
}

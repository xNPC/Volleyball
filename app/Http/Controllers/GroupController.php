<?php

namespace App\Http\Controllers;

use App\Models\StageGroup as Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function show(Group $group)
    {
        $group->load(['stage.tournament', 'teams' => function($query) {
            //$query->orderBy('name', 'desc');
        }]);

        return view('groups.show', compact('group'));
    }
}

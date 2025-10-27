<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function show(Group $group)
    {
        $group->load(['stage.tournament', 'teams' => function($query) {
            $query->orderBy('points', 'desc');
        }]);

        return view('groups.show', compact('group'));
    }
}

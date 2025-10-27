<?php

namespace App\Http\Controllers;

use App\Models\TournamentStage;
use App\Models\Tournament;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function show(Tournament $tournament, TournamentStage $stage)
    {
        $stage->load(['groups.teams' => function($query) {
            $query->orderBy('position', 'desc');
        }]);

        return view('stages.show', compact('tournament', 'stage'));
    }
}

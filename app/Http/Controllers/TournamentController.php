<?php

namespace App\Http\Controllers;

use App\Models\Tournament;

class TournamentController extends Controller
{
    public function show(Tournament $tournament)
    {
        $tournament->load(['stages' => function($query) {
            $query->orderBy('order');
        }]);

        return view('tournaments.show', compact('tournament'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tournament;

class TournamentController extends Controller
{
    public function show(Tournament $tournament)
    {
        $tournament->load([
            'stages' => function($query) {
                $query->orderBy('order');
            },
            'attachments' => function($query) {
                $query->where('group', 'tournament-documents')->orderBy('sort');
            }
        ]);

        return view('tournaments.show', compact('tournament'));
    }
}

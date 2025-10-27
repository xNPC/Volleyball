<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::withCount('stages')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament)
    {
        $tournament->load(['stages' => function($query) {
            $query->orderBy('order');
        }]);

        return view('tournaments.show', compact('tournament'));
    }
}

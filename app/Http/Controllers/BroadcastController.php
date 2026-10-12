<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;

class BroadcastController extends Controller
{
    public function index()
    {
        $relations = [
            'tournament',
            'game.homeApplication.team',
            'game.awayApplication.team',
            'game.venue',
            'cover',
        ];

        $live = Broadcast::query()
            ->with($relations)
            ->active()
            ->live()
            ->orderBy('sort_order')
            ->get();

        $upcoming = Broadcast::query()
            ->with($relations)
            ->active()
            ->upcoming()
            ->orderBy('scheduled_at')
            ->get();

        return view('broadcasts.index', compact('live', 'upcoming'));
    }

    public function show(Broadcast $broadcast)
    {
        abort_unless($broadcast->is_active, 404);

        $broadcast->load([
            'tournament',
            'game.homeApplication.team',
            'game.awayApplication.team',
            'game.venue',
            'cover',
        ]);

        return view('broadcasts.show', compact('broadcast'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        // Загружаем данные безопасно с проверкой отношений
        $user->load([
            'tournamentApplications' => function ($query) {
                $query->with([
                    'team',
                    'tournament',
                    'roster',
                ])->orderBy('tournament_applications.created_at', 'desc');
            },
            'applicationRosters' => function ($query) {
                $query->with([
                    'application.team',
                    'application.tournament',
                ])->orderBy('application_rosters.created_at', 'desc');
            },
        ]);

        return view('users.show', compact('user'));
    }
}

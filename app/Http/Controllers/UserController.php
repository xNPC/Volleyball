<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        // Загружаем данные безопасно с проверкой отношений
        $user->load([
            'tournamentApplications' => function($query) {
                $query->with([
                    'team',
                    'tournament',
                    'roster'
                ])->orderBy('created_at', 'desc');
            },
            'applicationRosters' => function($query) {
                $query->with([
                    'tournamentApplication.team',
                    'tournamentApplication.tournament'
                ])->orderBy('created_at', 'desc');
            }
        ]);

        return view('users.show', compact('user'));
    }
}

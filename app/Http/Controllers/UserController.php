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
                $query->whereNull('deleted_at')
                    ->whereHas('team', function ($q) {
                        $q->whereNull('deleted_at');
                    })
                    ->whereHas('tournament', function ($q) {
                        $q->whereNull('deleted_at');
                    })
                    ->with([
                        'team',
                        'tournament',
                        'roster',
                    ])->orderBy('created_at', 'desc');
            },
            'applicationRosters' => function ($query) {
                $query->whereHas('application', function ($q) {
                    $q->whereNull('deleted_at')
                        ->whereHas('team', function ($q2) {
                            $q2->whereNull('deleted_at');
                        })
                        ->whereHas('tournament', function ($q3) {
                            $q3->whereNull('deleted_at');
                        });
                })->with([
                    'application.team',
                    'application.tournament',
                ])->orderBy('created_at', 'desc');
            },
        ]);

        return view('users.show', compact('user'));
    }
}

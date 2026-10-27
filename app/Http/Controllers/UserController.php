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
                $query->withTrashed()
                    ->with([
                        'team' => function ($q) {
                            $q->withTrashed();
                        },
                        'tournament' => function ($q) {
                            $q->withTrashed();
                        },
                        'roster',
                    ])->orderBy('created_at', 'desc');
            },
            'applicationRosters' => function ($query) {
                $query->with([
                    'tournamentApplication' => function ($q) {
                        $q->withTrashed()->with([
                            'team' => function ($q2) {
                                $q2->withTrashed();
                            },
                            'tournament' => function ($q3) {
                                $q3->withTrashed();
                            },
                        ]);
                    },
                ])->orderBy('created_at', 'desc');
            },
        ]);

        return view('users.show', compact('user'));
    }
}

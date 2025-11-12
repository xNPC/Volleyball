<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'all');

        $users = User::withCount([
            'approvedTournamentApplications as approved_applications_count',
            'tournamentApplications as total_applications_count'
        ])
            ->when($search, function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($filter === 'with_teams', function($query) {
                $query->has('approvedTournamentApplications');
            })
            ->when($filter === 'new', function($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->when($filter === 'verified', function($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->orderBy('name')
            ->paginate(2)
            ->onEachSide(1)
            ->withQueryString();

        // Передаем статистику в шаблон
        $stats = [
            'total_users' => User::count(),
            'new_users_month' => User::where('created_at', '>=', now()->subMonth())->count(),
            'users_with_applications' => User::has('approvedTournamentApplications')->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
        ];

        return view('users.index', compact('users', 'search', 'filter', 'stats'));
    }

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


        // Безопасная загрузка с проверкой отношений
//        $user->load([
//            'tournamentApplications' => function($query) {
//                $query->with([
//                    'team',
//                    'tournament',
//                    'roster'
//                ])->orderBy('created_at', 'desc');
//            },
//            'applicationRosters.tournamentApplication.team'
//        ]);

        return view('users.show', compact('user'));
    }
}

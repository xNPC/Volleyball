<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TournamentTeamController;
use App\Http\Controllers\GalleryController;
use App\Livewire\TournamentList;
use App\Livewire\TournamentTeamList;
use App\Livewire\UserList;
use App\Livewire\TeamList;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Турниры
Route::get('/tournaments', TournamentList::class)->name('tournaments.index');
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
Route::get('/tournaments/{tournament}/teams', TournamentTeamList::class)->name('tournaments.teams');

// Состав команды в турнире
Route::get('/tournaments/{tournament}/teams/{team}/roster', [TournamentTeamController::class, 'roster'])->name('tournaments.teams.roster');

// Этапы
Route::get('/tournaments/{tournament}/stages/{stage}', [StageController::class, 'show'])->name('stages.show');

// Группы
Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

// Пользователи
Route::get('/users', UserList::class)->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

// Команды
Route::get('/teams', TeamList::class)->name('teams.index');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

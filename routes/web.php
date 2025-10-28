<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', HomePage::class)->name('home');

// Турниры
Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
Route::get('/tournaments/{tournament}/teams', [TournamentController::class, 'teams'])->name('tournaments.teams');

// Этапы
Route::get('/tournaments/{tournament}/stages/{stage}', [StageController::class, 'show'])->name('stages.show');

// Группы
Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

// Пользователи
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

// Команды
Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
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



//Route::middleware(['auth'])->group(function () {
//    Route::get('/profile', function () {
//        return view('profile');
//    })->name('profile.show');
//});

<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentDocumentController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileGamesController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TournamentTeamController;
use App\Http\Controllers\GalleryController;
use App\Livewire\TournamentList;
use App\Livewire\TournamentTeamList;
use App\Livewire\UserList;
use App\Livewire\TeamList;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tournaments', TournamentList::class)->name('tournaments.index');
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
Route::get('/tournaments/{tournament}/teams', TournamentTeamList::class)->name('tournaments.teams');
Route::get('/tournaments/{tournament}/documents/{attachment}', [TournamentDocumentController::class, 'download'])->name('tournaments.documents.download');

Route::get('/tournaments/{tournament}/teams/{team}/roster', [TournamentTeamController::class, 'roster'])->name('tournaments.teams.roster');

Route::get('/tournaments/{tournament}/stages/{stage}', [StageController::class, 'show'])->name('stages.show');

Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

Route::get('/users', UserList::class)->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

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

    Route::get('/applications/{application}/download', [ApplicationDocumentController::class, 'download'])
        ->name('applications.download');

    Route::get('/applications/{application}/download/medical', [ApplicationDocumentController::class, 'downloadMedical'])
        ->name('applications.download.medical');

    Route::get('/profile', [ProfileGamesController::class, 'index'])->name('profile.index');
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

Route::view('/privacy', 'privacy')->name('privacy');

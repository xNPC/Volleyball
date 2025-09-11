<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\Organization\OrganizationListScreen;
use App\Orchid\Screens\Organization\OrganizationEditScreen;
use App\Orchid\Screens\Venue\VenueEditScreen;
use App\Orchid\Screens\Venue\VenueListScreen;

use App\Orchid\Screens\Tournament\GroupScreen;
use App\Orchid\Screens\Tournament\TournamentListScreen;
use App\Orchid\Screens\Tournament\TournamentEditScreen;
use App\Orchid\Controllers\TournamentController;
//use App\Orchid\Screens\Team\TeamListScreen;
use App\Orchid\Screens\Team\TeamEditScreen;
use App\Orchid\Controllers\TeamController;
//use App\Orchid\Screens\Match\MatchListScreen;
use App\Orchid\Screens\Match\MatchEditScreen;
use App\Orchid\Controllers\MatchController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

/**
 *
 * Работа с организациями
 *
 */
Route::screen('organizations/{organization}/venues/create', VenueEditScreen::class)
    ->name('platform.venues.create')
    ->breadcrumbs(fn (Trail $trail, $organization) => $trail
        ->parent('platform.organization.list')
        ->push('Создание зала', route('platform.venues.edit', $organization))
    );

Route::screen('organizations/{organization}/venues/{venue}/edit', VenueEditScreen::class)
    ->name('platform.venues.edit')
    ->breadcrumbs(fn (Trail $trail, $organization, $venue) => $trail
        ->parent('platform.venues.list', $organization)
        ->push($venue->name)
    );

Route::screen('organizations/{organization}/venues', VenueListScreen::class)
    ->name('platform.venues.list')
    ->breadcrumbs(fn (Trail $trail, $organization) => $trail
        ->parent('platform.organization.list')
        ->push('Список залов', route('platform.venues.list', $organization))
    );

Route::screen('organizations/{organization}/edit', OrganizationEditScreen::class)
    ->name('platform.organization.edit')
    ->breadcrumbs(fn (Trail $trail, $organization) => $trail
        ->parent('platform.organization.list')
        ->push($organization->name, route('platform.organization.edit', $organization))
    );

Route::screen('organizations/create', OrganizationEditScreen::class)
    ->name('platform.organization.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.organization.list')
        ->push('Создание организации')
    );

Route::screen('organizations', OrganizationListScreen::class)
    ->name('platform.organization.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Список организаций', route('platform.organization.list'))
    );

/**
 *
 * Работа с турнирами
 *
 */
Route::screen('tournament/stages/{stage}/groups/{group}', GroupScreen::class)
    ->name('platform.tournament.group');

Route::screen('tournaments/create', TournamentEditScreen::class)
    ->name('platform.tournaments.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.tournaments.list')
            ->push('Создание турнира')
    );

Route::screen('tournaments/{tournament}/edit', TournamentEditScreen::class)
    ->name('platform.tournaments.edit')
    ->breadcrumbs(fn (Trail $trail, $tournament) => $trail
            ->parent('platform.tournaments.list')
            ->push($tournament->name, route('platform.tournaments.edit', $tournament))
    );

Route::screen('tournaments', TournamentListScreen::class)
    ->name('platform.tournaments.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push('Список турниров', route('platform.tournaments.list'))
    );

/**
 *
 * Работа с командами
 *
 */
Route::post('teams/save/{team?}', [TeamController::class, 'save'])->name('platform.teams.save');
Route::screen('teams/create', TeamEditScreen::class)->name('platform.teams.create');
Route::screen('teams/{team}/edit', TeamEditScreen::class)->name('platform.teams.edit');
//Route::screen('teams', TeamListScreen::class)->name('platform.teams.list');

/**
 *
 * Работа с играми
 *
 */
Route::post('matches/save/{match?}', [MatchController::class, 'save'])->name('platform.matches.save');
Route::screen('matches/create', MatchEditScreen::class)->name('platform.matches.create');
Route::screen('matches/{match}/edit', MatchEditScreen::class)->name('platform.matches.edit');
//Route::screen('matches', MatchListScreen::class)->name('platform.matches.list');


// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Route::screen('idea', Idea::class, 'platform.screens.idea');

<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Перейти на сайт')
                ->slug('home')
                ->icon('house')
                ->route('home'),

            Menu::make('Организации')
                ->slug('organizations')
                ->icon('building')
                ->title('Управление турнирами')
//                ->list([
//                    Menu::make('Список организаций')
//                        ->icon('building')
//                        ->route('platform.organization.list'),
//                ])
                ->route('platform.organization.list')
                ->permission('platform.organizations'),


            Menu::make('Турниры')
                ->slug('tournaments')
                ->icon('menu-button-wide-fill')
//                ->list([
//                    Menu::make('Список турниров')
//                        ->icon('trophy')
//                        ->route('platform.tournaments.list'),
//                ]),
            ->route('platform.tournaments.list')
            ->permission('platform.tournaments'),

            Menu::make('Команды')
                ->slug('teams')
                ->icon('people')
//                ->list([
//                    Menu::make('Создать')
//                        ->icon('plus')
//                        ->route('platform.teams.create'),
//                    Menu::make('Список команд')
//                        ->icon('list')
//                        ->route('platform.teams.list'),
//                ])
                ->route('platform.teams.list'),

            Menu::make('Заявки')
                ->slug('applications')
                ->icon('card-checklist')
//                ->list([
//                    Menu::make('Подать заявку')
//                        ->icon('plus')
//                        ->route('platform.main'),
//                    Menu::make('Список заявок')
//                        ->icon('list')
//                        ->route('platform.main'),
//                ])
                ->route('platform.applications.list'),

            Menu::make('Управление играми')
                ->icon('controller')
                ->route('platform.tournament.games.management')
                ->permission(['platform.games.result', 'platform.games.edit']),

//            Menu::make('Get Started')
//                ->icon('bs.book')
//                ->title('Navigation')
//                ->route(config('platform.index')),
//
//            Menu::make('Sample Screen')
//                ->icon('bs.collection')
//                ->route('platform.example')
//                ->badge(fn () => 6),
//
//            Menu::make('Form Elements')
//                ->icon('bs.card-list')
//                ->route('platform.example.fields')
//                ->active('*/examples/form/*'),
//
//            Menu::make('Layouts Overview')
//                ->icon('bs.window-sidebar')
//                ->route('platform.example.layouts'),
//
//            Menu::make('Grid System')
//                ->icon('bs.columns-gap')
//                ->route('platform.example.grid'),
//
//            Menu::make('Charts')
//                ->icon('bs.bar-chart')
//                ->route('platform.example.charts'),
//
//            Menu::make('Cards')
//                ->icon('bs.card-text')
//                ->route('platform.example.cards')
//                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group('Организации')
                ->addPermission('platform.organizations', 'Управление организациями'),

            ItemPermission::group('Турниры')
                ->addPermission('platform.tournaments', 'Управление турнирами'),

            ItemPermission::group('Команды')
                ->addPermission('platform.teams.create', 'Создание')
                ->addPermission('platform.teams.edit', 'Редактирование всех')
                ->addPermission('platform.teams.delete', 'Удаление'),

            ItemPermission::group('Заявки')
                ->addPermission('platform.applications.edit', 'Управление заявками')
                ->addPermission('platform.applications.delete', 'Удаление заявок'),

            ItemPermission::group('Игры')
                ->addPermission('platform.games.edit', 'Управление играми')
                ->addPermission('platform.games.result', 'Внесение результатов'),

//            ItemPermission::group('Разобрать')
//                ->addPermission('platform.1', '1')
        ];
    }
}

<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
use App\Orchid\Layouts\Team\TeamListTable;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class TeamListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        static $teams;

        if(auth()->user()->hasAccess('platform.teams.edit'))

            $teams = Team::paginate();

        $userTeams = Team::where('captain_id', '=', auth()->user()->id)->get();

        return [
            'teams' => $teams,
            'user_teams' => $userTeams,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список команд';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::tabs([
//                'Мои команды' => Layout::table('user_teams', [
//                    TD::make('name', 'Название'),
//                    TD::make('Действия')
//                        ->render(function (Team $team) {
//                            return
//                                Group::make([
//                                    Button::make('Редактировать')
//                                        ->icon('pencil')
//                                        ->type(Color::PRIMARY),
//                                    Button::make('Удалить')
//                                        ->icon('trash')
//                                        ->type(Color::DANGER)
//                                ])->autoWidth();
//                        }),
//                ]),
//                'Все команды' => Layout::table('teams', [
//                    TD::make('name', 'Название'),
//                    TD::make('Действия')
//                        ->render(function (Team $team) {
//                            return
//                                Group::make([
//                                    Button::make('Редактировать')
//                                        ->icon('pencil')
//                                        ->type(Color::PRIMARY),
//                                    Button::make('Удалить')
//                                        ->icon('trash')
//                                        ->type(Color::DANGER)
//                                ])->autoWidth();
//                        }),
//                ])
//                    ->canSee(auth()->user()->hasAccess('platform.teams.edit')),
                'Мои команды' => new TeamListTable('user_teams'),
                'Все команды' => new TeamListTable('teams')
            ])

        ];
    }
}

<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
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
        $teams = Team::where('captain_id', '=', auth()->user()->id)->get();

        return [
            'teams' => $teams
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
            Layout::table('teams', [
                TD::make('name', 'Название'),
                TD::make('Действия')
                    ->render(function (Team $team) {
                        return
                            Group::make([
                                Button::make('Редактировать')
                                    ->icon('pencil')
                                    ->type(Color::PRIMARY),
                                Button::make('Удалить')
                                    ->icon('trash')
                                    ->type(Color::DANGER)
                            ])->autoWidth()
                            ;
                    }),
            ])
        ];
    }
}

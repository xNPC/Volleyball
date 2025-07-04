<?php

namespace App\Orchid\Screens\Tournament;

use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class TournamentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tournaments' => new Repository([
                'id' => '1',
                'name' => 'Первый и самый главный',
                'date_start' => '2019-01-01',
                'date_end' => '2019-01-31',
                'format' => '4+2',
            ]),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Турниры';
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
            Layout::table('tournaments', [
                TD::make('tournaments.id', 'ID'),
//                TD::make('name', 'Название'),
//                TD::make('date_start', 'Начало'),
//                TD::make('date_end', 'Окончание'),
//                TD::make('format', 'Формат')
            ])
        ];
    }
}

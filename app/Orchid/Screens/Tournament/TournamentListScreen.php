<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Tournament;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

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
            'tournaments' => Tournament::with('organization')
                ->filters()
                ->defaultSort('start_date', 'desc')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список турниров';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать турнир')
                ->icon('plus')
                ->route('platform.tournaments.create'),
        ];
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
                TD::make('id', 'ID')
                    ->sort(),

                TD::make('name', 'Название')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(function (Tournament $tournament) {
                        return Link::make($tournament->name)
                            ->route('platform.tournament.edit', $tournament);
                    }),

                TD::make('organization.name', 'Организация')
                    ->filter(TD::FILTER_TEXT),

                TD::make('start_date', 'Дата начала')
                    ->sort()
                    ->filter(TD::FILTER_DATE)
                    ->render(function (Tournament $tournament) {
                        return $tournament->start_date->format('d.m.Y');
                    }),

                TD::make('status', 'Статус')
                    ->sort()
                    ->filter(TD::FILTER_SELECT)
                    ->filterOptions([
                        'planned' => 'Запланирован',
                        'ongoing' => 'В процессе',
                        'completed' => 'Завершен'
                    ])
                    ->render(function (Tournament $tournament) {
                        return [
                            'planned' => '<span class="text-warning">Запланирован</span>',
                            'ongoing' => '<span class="text-success">В процессе</span>',
                            'completed' => '<span class="text-secondary">Завершен</span>'
                        ][$tournament->status];
                    })
                    ->align(TD::ALIGN_CENTER),

                TD::make('created_at', 'Создан')
                    ->sort()
                    ->render(function (Tournament $tournament) {
                        return $tournament->created_at->format('d.m.Y H:i');
                    })
            ])
        ];
    }
}

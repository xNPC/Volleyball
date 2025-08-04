<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateRange;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class TournamentEditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
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

    public $tournament;

    public function name(): ?string
    {
        return $this->tournament->exists ? 'Редактирование турнира' : 'Создание турнира';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Select::make('tournament.organization_id')
                        ->fromModel(Organization::class, 'name')
                        ->title('Организация')
                        ->required(),

                    Input::make('tournament.name')
                        ->title('Название')
                        ->required(),

                    DateRange::make('tournament.dates')
                        ->title('Даты проведения')
                        ->required(),

                    Select::make('tournament.status')
                        ->options([
                            'planned' => 'Запланирован',
                            'ongoing' => 'В процессе',
                            'completed' => 'Завершен'
                        ])
                        ->title('Статус'),
                ]),

                'Этапы' => Layout::rows([
                    \App\Orchid\Layouts\Tournament\TournamentStagesLayout::class
                ]),
            ])
        ];
    }

}

<?php

namespace App\Orchid\Screens\Match;

use App\Models\Mmatch;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTime;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class MatchEditScreen extends Screen
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

    public $match;

    public function name(): ?string
    {
        return $this->match->exists ? 'Редактирование матча' : 'Создание матча';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Select::make('match.stage_id')
                        ->fromModel(TournamentStage::class, 'name')
                        ->title('Этап турнира')
                        ->required(),

                    Select::make('match.home_application_id')
                        ->fromModel(TournamentApplication::class, 'team.name')
                        ->title('Хозяева')
                        ->required(),

                    Select::make('match.away_application_id')
                        ->fromModel(TournamentApplication::class, 'team.name')
                        ->title('Гости')
                        ->required(),

                    DateTime::make('match.scheduled_time')
                        ->title('Дата и время')
                        ->required(),
                ]),

                'Результаты' => Layout::rows([
                    \App\Orchid\Layouts\Match\MatchSetsLayout::class
                ])
            ])
        ];
    }
}

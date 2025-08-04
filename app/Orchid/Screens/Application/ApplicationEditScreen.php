<?php

namespace App\Orchid\Screens\Application;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\Venue;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class ApplicationEditScreen extends Screen
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

    public $application;

    public function name(): ?string
    {
        return $this->application->exists ? 'Редактирование заявки' : 'Подача заявки';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Select::make('application.tournament_id')
                        ->fromModel(Tournament::class, 'name')
                        ->title('Турнир')
                        ->required(),

                    Select::make('application.team_id')
                        ->fromModel(Team::class, 'name')
                        ->title('Команда')
                        ->required(),

                    Select::make('application.venue_id')
                        ->fromModel(Venue::class, 'name')
                        ->title('Предпочитаемый зал')
                        ->required(),

                    CheckBox::make('application.is_complete')
                        ->title('Заявка завершена')
                        ->sendTrueOrFalse(),
                ]),

                'Расписание' => Layout::rows([
                    \App\Orchid\Layouts\Application\ApplicationScheduleLayout::class
                ]),

                'Состав' => Layout::rows([
                    \App\Orchid\Layouts\Application\ApplicationRosterLayout::class
                ])
            ])
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Tournament;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateRange;

class TournamentStagesLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    public function fields(): array
    {
        return [
            Repeater::make('tournament.stages')
                ->title('Этапы турнира')
                ->fields([
                    Input::make('name')
                        ->title('Название этапа')
                        ->required(),

                    Select::make('stage_type')
                        ->options([
                            'group' => 'Групповой',
                            'playoff' => 'Плейофф'
                        ])
                        ->title('Тип этапа'),

                    DateRange::make('dates')
                        ->title('Даты этапа'),
                ])
        ];
    }

}

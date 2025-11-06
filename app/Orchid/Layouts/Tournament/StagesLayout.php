<?php

namespace App\Orchid\Layouts\Tournament;

use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class StagesLayout extends Rows
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

            Input::make('stage.id')
                ->type('hidden'),

            Input::make('stage.name')
                ->title('Название')
                ->required()
                ->placeholder('Групповой этап'),

            Select::make('stage.stage_type')
                ->options([
                    'group' => 'Групповой',
                    'playoff' => 'Плейофф',
                    'qualification' => 'Квалификация'
                ])
                ->title('Тип'),

            Input::make('stage.order')
                ->title('Порядок')
                ->type('number')
                ->min(1),

            Group::make([
                DateTimer::make('stage.start_date')
                    ->title('Дата начала')
                    ->required()
                    ->format('d.m.Y')
                    ->placeholder('Выберите дату'),

                DateTimer::make('stage.end_date')
                    ->title('Дата окончания')
                    ->required()
                    ->format('d.m.Y')
                    ->placeholder('Выберите дату'),
            ])
                ->autoWidth(),
        ];
    }

}

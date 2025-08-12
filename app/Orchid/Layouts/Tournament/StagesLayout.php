<?php

namespace App\Orchid\Layouts\Tournament;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Field;

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
            Matrix::make('tournament.stages')
                ->title('Этапы турнира')
                ->columns([
                    'Название' => 'name',
                    'Тип' => 'stage_type',
                    'Порядок' => 'order'
                ])
                ->fields([
                    'name' => Input::make('name')
                        ->required()
                        ->placeholder('Групповой этап'),

                    'stage_type' => Select::make('stage_type')
                        ->options([
                            'group' => 'Групповой',
                            'playoff' => 'Плейофф',
                            'qualification' => 'Квалификация'
                        ]),

                    'order' => Input::make('order')
                        ->type('number')
                        ->min(1)
                ])
        ];
    }

}

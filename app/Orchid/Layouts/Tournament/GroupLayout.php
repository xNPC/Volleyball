<?php

namespace App\Orchid\Layouts\Tournament;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Layouts\Rows;

class GroupLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Matrix::make('stage.groups')
                ->title('Группы этапа')
                ->columns([
                    'Название' => 'name',
                    'Кол-во команд' => 'team_count'
                ])
                ->fields([
                    'name' => Input::make()->required(),
                    'team_count' => Input::make()
                        ->type('number')
                        ->min(2)
                ])
        ];
    }
}

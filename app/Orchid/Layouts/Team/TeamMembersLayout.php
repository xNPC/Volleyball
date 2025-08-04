<?php

namespace App\Orchid\Layouts\Team;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Rows;

class TeamMembersLayout extends Rows
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
            Repeater::make('team.members')
                ->title('Игроки команды')
                ->fields([
                    Select::make('user_id')
                        ->fromModel(User::class, 'name')
                        ->title('Игрок')
                        ->required(),

                    Input::make('jersey_number')
                        ->title('Игровой номер')
                        ->type('number'),

                    Select::make('position')
                        ->options([
                            'setter' => 'Связующий',
                            'outside' => 'Доигровщик',
                            'opposite' => 'Диагональный',
                            'middle' => 'Центральный',
                            'libero' => 'Либеро'
                        ])
                        ->title('Позиция'),

                    CheckBox::make('is_captain')
                        ->title('Капитан команды')
                        ->sendTrueOrFalse(),
                ])
        ];
    }
}

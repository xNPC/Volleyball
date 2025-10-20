<?php

namespace App\Orchid\Layouts\Application;

use App\Models\ApplicationRoster;
use App\Models\User;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class AddPlayerLayout extends Rows
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
            Relation::make('roster')
                ->fromModel(User::class, 'name')
                //->displayAppend('display_name')
                ->title('Игрок')
                ->help('')
                ->allowEmpty()
                ->required(),


            Group::make([
                Input::make('roster.jersey_number')
                    ->title('Игровой номер'),

                Select::make('roster.player.position')
                    ->options([
                        'Диагональный' => 'Диагональный',
                        'Доигровщик' => 'Доигровщик',
                        'Центральный блокирующий' => 'Центральный блокирующий',
                        'Связующий' => 'Связующий',
                        'Либеро' => 'Либеро'
                    ])
                    ->title('Амплуа')
            ])
            ->widthColumns('30% 65%')

        ];
    }
}

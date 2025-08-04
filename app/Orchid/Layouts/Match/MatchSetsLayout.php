<?php

namespace App\Orchid\Layouts\Match;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class MatchSetsLayout extends Rows
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
            Repeater::make('match.sets')
                ->title('Сеты')
                ->fields([
                    Input::make('home_score')
                        ->title('Хозяева')
                        ->type('number')
                        ->required(),

                    Input::make('away_score')
                        ->title('Гости')
                        ->type('number')
                        ->required(),
                ])
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Application;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Time;
use Orchid\Screen\Layouts\Rows;

class ApplicationScheduleLayout extends Rows
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
        $days = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье'
        ];

        return [
            Repeater::make('application.schedules')
                ->title('Предпочитаемое время игр')
                ->fields([
                    Select::make('day_of_week')
                        ->options($days)
                        ->title('День недели')
                        ->required(),

                    Time::make('start_time')
                        ->title('Время начала')
                        ->required(),

                    Time::make('end_time')
                        ->title('Время окончания')
                        ->required(),
                ])
        ];
    }
}

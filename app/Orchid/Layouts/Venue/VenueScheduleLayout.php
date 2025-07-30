<?php

namespace App\Orchid\Layouts\Venue;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Time;
use Orchid\Screen\Layouts\Rows;

class VenueScheduleLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    public function fields(): array
    {
        $days = [
            1 => 'Пн', 2 => 'Вт', 3 => 'Ср',
            4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'
        ];

        return collect($days)->map(function ($day, $index) {
            return [
                Time::make("schedules.{$index}.start_time")
                    ->title("{$day} - Начало")
                    ->value('09:00'),

                Time::make("schedules.{$index}.end_time")
                    ->title("{$day} - Конец")
                    ->value('22:00'),

                CheckBox::make("schedules.{$index}.is_available")
                    ->title('Доступен')
                    ->value(true)
                    ->sendTrueOrFalse(),
            ];
        })->flatten(1)->toArray();
    }
}

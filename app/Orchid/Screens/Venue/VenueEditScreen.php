<?php

namespace App\Orchid\Screens\Venue;

use App\Models\Venue;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;

class VenueEditScreen extends Screen
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

    public $venue;

    public function name(): ?string
    {
        return $this->venue->exists ? 'Редактирование зала' : 'Создание зала';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Select::make('venue.organization_id')
                        ->fromModel(Organization::class, 'name')
                        ->title('Организация')
                        ->required(),

                    Input::make('venue.name')
                        ->title('Название зала')
                        ->required(),

                    Input::make('venue.address')
                        ->title('Адрес'),

                    Input::make('venue.capacity')
                        ->title('Вместимость')
                        ->type('number'),
                ]),

                'Расписание' => Layout::rows([
                    \App\Orchid\Layouts\Venue\VenueScheduleLayout::class
                ]),
            ])
        ];
    }

}

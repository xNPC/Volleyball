<?php

namespace App\Orchid\Screens\Venue;

use App\Models\Organization;
use App\Models\Venue;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;

class VenueEditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */

    public function permission(): ?iterable
    {
        return [
            'platform.organizations'
        ];
    }

    public function query(Venue $venue): iterable
    {
        return [
            'venue' => $venue
        ];
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

            Layout::rows([
                Input::make('venue.id')
                    ->type('hidden'),

                Input::make('venue.name')
                    ->title('Название')
                    ->type('text')
                    ->required(),

                Input::make('venue.address')
                    ->title('Адрес')
                    ->type('text')
                    ->required(),

                Button::make('Сохранить')
                    ->icon('check')
                    ->method('createOrUpdateVenue')
                    ->type(Color::PRIMARY)

            ])
        ];
    }

    public function createOrUpdateVenue(Venue $venue, Organization $organization, Request $request)
    {

        $venueId = $request->input('venue.id');

        $validated = $request->validate([
            'venue.name' => 'required|string|max:255',
            'venue.address' => 'required|string|max:255',
        ]);

        Venue::updateOrCreate([
            'id' => $venueId,
            'organization_id' => $organization->id,
        ],
            $validated['venue']
        );

        Toast::info('Успешно сохранено');

        return redirect()->route('platform.venues.list', $organization);
    }

}

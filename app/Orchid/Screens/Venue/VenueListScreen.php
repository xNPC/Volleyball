<?php

namespace App\Orchid\Screens\Venue;

use App\Models\Organization;
use App\Models\Venue;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class VenueListScreen extends Screen
{
    public $organization;
    public $venue;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Organization $organization): iterable
    {
        return [
            'venues' => $organization->venues()->paginate(),
            'organization' => $organization
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список залов';
    }

    public function description(): ?string
    {
        return $this->organization->name;
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

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('venues', [
                TD::make('name', 'Название'),
                TD::make('address', 'Адрес'),
                TD::make('Действия')
                    ->render(fn (Venue $venue) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->route('platform.venues.edit', [
                                    'organization' => $this->organization,
                                    'venue' => '1'
                                ])
                        ])
                    )
            ])
        ];
    }
}

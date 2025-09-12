<?php

namespace App\Orchid\Screens\Organization;

use App\Models\Organization;
use App\Models\Venue;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class OrganizationListScreen extends Screen
{

    public function name(): ?string
    {
        return 'Список организаций';
    }

    public function query(): array
    {
        return [
            'organizations' => Organization::filters()->paginate()
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('organizations', [
                TD::make('id', 'ID'),
                TD::make('name', 'Название'),
                TD::make('contact_email', 'Email'),
                TD::make('contact_phone', 'Телефон'),
                TD::make('actions', 'Действия')
                    ->render(fn (Organization $organization) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->icon('bs.pencil')
                                ->route('platform.organization.edit', $organization),

                            Link::make('Залы')
                                ->icon('bs.houses')
                                ->route('platform.venues.list', $organization)
                        ])

                    )
            ]),
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать')
                ->icon('plus')
                ->route('platform.organization.create')
        ];
    }

}

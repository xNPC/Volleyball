<?php

namespace App\Orchid\Screens\Organization;

use App\Models\Organization;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;

class OrganizationListScreen extends Screen
{

    public function name(): ?string
    {
        return 'Все организации';
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
                    ->render(function (Organization $organization) {
                        return Link::make('Редактировать')
                            ->route('platform.organizations.edit', $organization);
                    })
            ])
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

}

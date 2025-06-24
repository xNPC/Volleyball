<?php

namespace App\Orchid\Layouts\Organization;

use App\Models\Organization;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use function Termwind\render;

class OrganizationListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'organizations';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('name', 'Название')->cantHide(),
            TD::make('status', 'Статус')
                ->render(function (Organization $organization) {
                    return $organization->status === 'active' ? 'Активная' : 'Не активная';
                })
                ->cantHide()
                ->sort(),
            TD::make('action', 'Действия')
                ->render(function (Organization $organization) {
                    return ModalToggle::make(' ')
                        ->modal('editOrganization')
                        ->method('createOrUpdateOrganization')
                        ->modalTitle('Редактирование организации ' . $organization->name)
                        ->asyncParameters([
                            'organization' => $organization->id
                        ])
                        ->icon('pencil');
            })
        ];
    }
}

<?php

namespace App\Orchid\Screens\Organization;

use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;
use App\Orchid\Layouts\Organization\OrganizationListTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class OrganizationListScreen2 extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'organizations' => Organization::filters()->defaultSort('status', 'asc')->paginate(10)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Организации';
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Управление организациями';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить организацию')
                ->modal('createOrganization')
                ->method('createOrUpdateOrganization')
                ->class('btn btn-success')
                ->icon('plus')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            OrganizationListTable::class,
            Layout::modal('createOrganization', Layout::rows([
                Input::make('organization.name')
                    ->title('Название')
                    ->required(),
                Input::make('organization.status')->type('hidden')
//                Select::make('organization.status')
//                    ->options(['active'=>'Активная', 'inactive'=>'Не активная'])
//                    ->title('Статус')
//                    ->required()
            ]))
                ->title('Создание организации')
                ->applyButton('Создать'),
            Layout::modal('editOrganization', Layout::rows([
                Input::make('organization.id')->type('hidden'),
                Input::make('organization.name')
                    ->required()
                    ->placeholder('Название организации')
                    ->title('Название'),
                Select::make('organization.status')
                    ->required()
                    ->options([
                        'active'=>'Активная',
                        'inactive'=>'Не активная',
                    ])
                    ->title('Статус')
                    ->help('Включение или выключение организации')
            ]))->title('Редактировать организацию')
            ->async('asyncGetOrganization')
        ];
    }

    public function asyncGetOrganization(Organization $organization): array
    {
        return [
            'organization' => $organization
        ];
    }

    public function update(Request $request)
    {
        Organization::find($request->input('organization.id'))->update($request->organization);
        Toast::info('Организация успешно обновлена');
    }

    public function create(OrganizationRequest $request): void
    {
        Organization::create(array_merge($request->validated(), [
            'status' => 'active'
        ]));
        Toast::info('Организация успешно добавлена');
    }

    public function createOrUpdateOrganization(OrganizationRequest $request): void
    {
        $organizationId = $request->input('organization.id');
        Organization::updateOrCreate([
            'id' => $organizationId
        ], array_merge($request->validated()['organization'], [
            'status' => is_null($request->input('organization.status')) ? 'active' : $request->input('organization.status')
        ]));

        is_null($organizationId) ? Toast::info('Организация успешно создана') : Toast::info('Организация успешно обновлена');
    }
}

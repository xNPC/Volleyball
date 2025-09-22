<?php

namespace App\Orchid\Screens\Organization;

use App\Models\Organization;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;

class OrganizationEditScreen extends Screen
{
    public $organization;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Organization $organization): iterable
    {
        return [
            'organization' => $organization,
            'logo' => $organization->attachment()->first()
        ];
    }

    public function name(): ?string
    {
        return $this->organization->exists ? 'Редактирование организации' : 'Создание организации';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save'),

            Button::make('Удалить')
                ->icon('trash')
                ->method('remove')
                ->confirm('Отменить удаление будет нельзя!')
                ->canSee($this->organization->exists),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('organization.name')
                    ->title('Название')
                    ->required(),

                TextArea::make('organization.description')
                    ->title('Описание')
                    ->rows(3),

                Input::make('organization.contact_email')
                    ->title('Контактный Email')
                    ->type('email'),

                Input::make('organization.contact_phone')
                    ->title('Контактный телефон')
                    ->mask('+7 (999) 999-9999')
                    ->type('Phone'),

                Upload::make('organization.attachment')
                    ->title('Логотип')
                    ->acceptedFiles('image/*')
                    ->targetId()
            ])
        ];
    }

    public function save(Organization $organization, Request $request)
    {
        $request->validate([
            'organization.name' => 'required|string|max:255',
            'organization.attachment' => 'nullable|mimes:jpg,jpeg,png|max:10240'
        ]);

        $organization->fill($request->input('organization'))->save();

        if ($request->has('organization.attachment')) {
            $organization->attachment()->sync(
                $request->input('organization.attachment')
            );
        }

        return redirect()->route('platform.organization.list');
    }

    /**
     * Remove the organization
     */
    public function remove(Organization $organization)
    {
        $organization->delete();
        return redirect()->route('platform.organization.list');
    }
}

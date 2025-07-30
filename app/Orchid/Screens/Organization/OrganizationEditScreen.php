<?php

namespace App\Orchid\Screens\Organization;

use App\Models\Organization;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;

class OrganizationEditScreen extends Screen
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

    public $organization;

    public function name(): ?string
    {
        return $this->organization->exists ? 'Редактирование' : 'Создание организации';
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
                    ->title('Контактный телефон'),

                Upload::make('organization.logo')
                    ->title('Логотип')
                    ->acceptedFiles('image/*')
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

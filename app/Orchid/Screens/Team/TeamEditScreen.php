<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class TeamEditScreen extends Screen
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

    public $team;

    public function name(): ?string
    {
        return $this->team->exists ? 'Редактирование команды' : 'Создание команды';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Input::make('team.name')
                        ->title('Название команды')
                        ->required(),

                    Input::make('team.city')
                        ->title('Город'),

                    Upload::make('team.logo')
                        ->title('Логотип')
                        ->acceptedFiles('image/*'),

                    Input::make('team.description')
                        ->title('Описание')
                        ->type('textarea'),
                ]),

                'Состав' => Layout::rows([
                    \App\Orchid\Layouts\Team\TeamMembersLayout::class
                ]),
            ])
        ];
    }
}

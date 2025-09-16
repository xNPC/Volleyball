<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class TeamEditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Team $team): iterable
    {
        return [
            'team' => $team
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

//                    Upload::make('team.logo')
//                        ->title('Логотип')
//                        ->acceptedFiles('image/*'),

                    TextArea::make('team.description')
                        ->title('Описание')
                        ->rows(4),

                    Button::make('Сохранить')
                        ->icon('check')
                        ->type(Color::SUCCESS)
                ]),

//                'Состав' => Layout::rows([
//                    \App\Orchid\Layouts\Team\TeamMembersLayout::class
//                ]),
            ])
        ];
    }
}

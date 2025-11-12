<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

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
                    Input::make('team.id')
                        ->type('hidden'),

                    Input::make('team.name')
                        ->title('Название команды')
                        ->required(),

                    Relation::make('team.captain_id')
                        ->fromModel(User::class, 'name')
                        ->value(auth()->user()->id)
                        ->title('Капитан')
                        ->required()
                        ->allowEmpty()
                        ->help('Выберите капитана')
                        ->disabled(!auth()->user()->hasAccess('platform.teams.edit')),

//                    Upload::make('team.logo')
//                        ->title('Логотип')
//                        ->acceptedFiles('image/*'),

                    TextArea::make('team.description')
                        ->title('Описание')
                        ->rows(4),

                    Group::make([

                        Button::make('Сохранить')
                            ->method('createOrUpdateTeam')
                            ->icon('check')
                            ->type(Color::SUCCESS),

//                        Button::make('Удалить')
//                            ->method('remove')
//                            ->icon('trash')
//                            ->type(Color::DANGER)
//                            ->canSee($this->team->exists)
//                            ->confirm('Вместе с командой удалится вся ее история - заявки, игры и т.д., Вы точно хотите удалить команду?')
                    ])
                        ->autoWidth()
                ]),

//                'Состав' => Layout::rows([
//                    \App\Orchid\Layouts\Team\TeamMembersLayout::class
//                ]),
            ])
        ];
    }

    public function createOrUpdateTeam(Request $request)
    {
        $teamId = $request->input('team.id');
        $user = auth()->user();

        $validated = $request->validate([
            'team.name' => 'required|string|max:255',
            'team.captain_id' => 'integer|exists:users,id',
            'team.description' => 'nullable|string|max:2500',
        ]);

        $teamData = $validated['team'];

        // Автоматически назначаем капитаном, если нет прав
        if (!$user->hasAccess('platform.teams.edit')) {
            $teamData['captain_id'] = $user->id;

            // Проверяем доступ к редактированию существующей команды
            if ($teamId && Team::where('id', $teamId)->where('captain_id', '!=', $user->id)->exists()) {
                abort(403, 'Доступ запрещен');
            }
        }

        Team::updateOrCreate(['id' => $teamId], $teamData);

        Toast::info('Успешно сохранено');
        return redirect()->route('platform.teams.list');
    }

}

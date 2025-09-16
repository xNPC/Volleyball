<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\StageGroup;
use App\Models\TournamentStage;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GroupScreen extends Screen
{
    public $groups;
    public $stage;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(StageGroup $group, TournamentStage $stage): iterable
    {

        //$stage = $group->stage;

        return [
            'group' => $group->load('teams'),
            'stage' => $stage,
            'groups' => $stage->groups,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Группы ' . $this->stage->name;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить группу')
                ->icon('plus')
                ->modal('createOrUpdateGroup')
                ->method('createOrUpdateGroup'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        static $tabs = [];

        foreach ($this->groups as $gr)
        {
            $tabs[$gr->name] =
                Layout::columns([
                    Layout::table('groups', [
                        TD::make('name', 'Команда'),
                        TD::make('Действие')
                            ->render(fn() => Link::make('Убрать из группы'))
                    ]),
                    Layout::rows([
                        Group::make([
                            Button::make('Добавить команду')
                                ->type(Color::SUCCESS)
                                ->icon('plus'),
                            Button::make('Редактировать группу')
                                ->type(Color::PRIMARY)
                                ->icon('pencil'),
                            Button::make('Удалить группу')
                                ->type(Color::DANGER)
                                ->icon('trash'),
                        ])
                    ]),
                ]);
        }

        return [

            //dd(),

            Layout::modal('createOrUpdateGroup', [
                Layout::rows([
//                    Input::make('stage_id')
//                        ->type('integer'),
                    Input::make('group.name')
                        ->title('Название'),
                    Input::make('group.order')
                        ->title('Порядок')
                        ->type('number')
                        ->min(1)
                ])
            ])
            ->title('Создать или обновить группу'),

                Layout::tabs(
                    $tabs
                )

        ];
    }

    public function save(StageGroup $group, Request $request)
    {
        $group->fill($request->input('group'))->save();

        // Синхронизация команд
        $teams = collect($request->input('group.teams', []))
            ->mapWithKeys(fn($item) => [$item['id'] => ['position' => $item['pivot']['position']]]);

        $group->teams()->sync($teams);

        return redirect()->route('platform.tournament.stage', $group->stage_id);
    }

    public function createOrUpdateGroup(Request $request)
    {
        $groupId = $request->input('group.id');

        $validated = $request->validate([
            //'group.stage_id' => 'required|integer|exists:App\Models\TournamentStage,id',
            'group.name' => 'required|string|max:255',
            'group.order' => 'required|integer|min:1',
        ]);

        StageGroup::updateOrCreate([
            'id' => $groupId,
            'stage_id' => $this->stage->id,
        ], $validated['group']);

        Toast::info('Успешно сохранено');

    }

}

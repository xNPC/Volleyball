<?php
//
//namespace App\Orchid\Screens\Tournament;
//
//use App\Models\StageGroup;
//use App\Models\Team;
//use App\Models\Tournament;
//use App\Models\TournamentApplication;
//use App\Models\TournamentStage;
//use Illuminate\Http\Request;
//use Orchid\Screen\Actions\Button;
//use Orchid\Screen\Actions\Link;
//use Orchid\Screen\Actions\ModalToggle;
//use Orchid\Screen\Fields\Group;
//use Orchid\Screen\Fields\Input;
//use Orchid\Screen\Fields\Matrix;
//use Orchid\Screen\TD;
//use Orchid\Support\Color;
//use Orchid\Support\Facades\Layout;
//use Orchid\Screen\Screen;
//use Orchid\Support\Facades\Toast;
//
//class GroupScreen extends Screen
//{
//    public $groups;
//    public $stage;
//
//    public $teams;
//
//    //public $teamsInGroup;
//
//    public $tournament;
//
//    /**
//     * Fetch data to be displayed on the screen.
//     *
//     * @return array
//     */
//    public function query(Tournament $tournament,StageGroup $group, TournamentStage $stage): iterable
//    {
//
//        //$stage = $group->stage;
//
//        $teams = Team::withApprovedApplicationForTournament($tournament->id)->get();
//
//        //$teamsInGroup = Team::inGroupWithApprovedApplication($group->id, $tournament->id)->get();
//
//
//        return [
//            'group' => $group->load('teams'),
//            'stage' => $stage,
//            'groups' => $stage->load('groups.teams')->groups,
//            'tournament' => $tournament,
//
//            //'teamsInGroup' => $teamsInGroup,
//
//            'teams' => $teams,
//        ];
//    }
//
//    /**
//     * The name of the screen displayed in the header.
//     *
//     * @return string|null
//     */
//    public function name(): ?string
//    {
//        return 'Группы ' . $this->stage->name;
//    }
//
//    /**
//     * The screen's action buttons.
//     *
//     * @return \Orchid\Screen\Action[]
//     */
//    public function commandBar(): iterable
//    {
//        return [
//            ModalToggle::make('Добавить группу')
//                ->icon('plus')
//                ->modal('createOrUpdateGroup')
//                ->method('createOrUpdateGroup'),
//        ];
//    }
//
//    /**
//     * The screen's layout elements.
//     *
//     * @return \Orchid\Screen\Layout[]|string[]
//     */
//    public function layout(): iterable
//    {
//        static $tabs = [];
//
//        foreach ($this->groups as $gr)
//        {
//            $teamsInGroup = Team::inGroupWithApprovedApplication($gr->id, $this->tournament->id)->get();
//
//            $tabs[$gr->name] =
//                Layout::columns([
//                    Layout::table($teamsInGroup, [
//                        TD::make('name', 'Команда'),
//                        TD::make('Действие')
//                            ->render(fn() =>
//                                Button::make('Убрать из группы')
//                                    ->icon('trash')
//                                    ->method('removeTeamFromGroup', [
//                                        'group_id' => $gr->id,
//                                        //'team_id' => $this->teams->id,
//
//                                    ]),
//                            )
//                    ]),
//                    Layout::rows([
//                        Group::make([
//                            Button::make('Добавить команду')
//                                ->type(Color::SUCCESS)
//                                ->icon('plus')
//                                ->method('test'),
//                            Button::make('Редактировать группу')
//                                ->type(Color::PRIMARY)
//                                ->icon('pencil'),
//                            Button::make('Удалить группу')
//                                ->type(Color::DANGER)
//                                ->icon('trash'),
//                        ])
//                    ]),
//                ]);
//        }
//
//        return [
//
//            //dd(),
//
//            Layout::modal('createOrUpdateGroup', [
//                Layout::rows([
////                    Input::make('stage_id')
////                        ->type('integer'),
//                    Input::make('group.name')
//                        ->title('Название'),
//                    Input::make('group.order')
//                        ->title('Порядок')
//                        ->type('number')
//                        ->min(1)
//                ])
//            ])
//            ->title('Создать или обновить группу'),
//
//                Layout::tabs(
//                    $tabs
//                )
//
//        ];
//    }
//
//    public function save(StageGroup $group, Request $request)
//    {
//        $group->fill($request->input('group'))->save();
//
//        // Синхронизация команд
//        $teams = collect($request->input('group.teams', []))
//            ->mapWithKeys(fn($item) => [$item['id'] => ['position' => $item['pivot']['position']]]);
//
//        $group->teams()->sync($teams);
//
//        return redirect()->route('platform.tournament.stage', $group->stage_id);
//    }
//
//    public function createOrUpdateGroup(Request $request)
//    {
//        $groupId = $request->input('group.id');
//
//        $validated = $request->validate([
//            //'group.stage_id' => 'required|integer|exists:App\Models\TournamentStage,id',
//            'group.name' => 'required|string|max:255',
//            'group.order' => 'required|integer|min:1',
//        ]);
//
//        StageGroup::updateOrCreate([
//            'id' => $groupId,
//            'stage_id' => $this->stage->id,
//        ], $validated['group']);
//
//        Toast::info('Успешно сохранено');
//
//    }
//
//    public function removeTeamFromGroup(Request $request)
//    {
//        $groupId = $request->input('group_id');
//        $teamId = $request->input('team_id');
//
//        $group = StageGroup::find($groupId);
//        $group->teams()->detach($teamId);
//
//        Toast::info('Успешно удалено');
//    }
//
//    public function test() {
//        dd($this->tournament->id);
//    }
//
//}
//*/

namespace App\Orchid\Screens\Tournament;

use App\Models\GroupTeam;
use App\Models\StageGroup;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GroupScreen extends Screen
{
    public $stage;
    public $groups;
    public $tournament;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Tournament $tournament, TournamentStage $stage): iterable
    {
        // Загружаем этап с группами и их заявками
        $stage->load(['groups.teams.team']);

        // Для каждой группы собираем команды через заявки
        $groupsData = [];
        foreach ($stage->groups as $group) {
            $teamsInGroup = collect();

            foreach ($group->teams as $application) {
                if ($application->team) {
                    $teamsInGroup->push($application->team);
                }
            }

            $groupsData[$group->id] = $teamsInGroup;
        }

        return [
            'stage' => $stage,
            'tournament' => $tournament,
            'groups' => $stage->groups,
            'groupsData' => $groupsData,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Группы этапа: ' . $this->stage->name . ', турнира: ' . $this->tournament->name;
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
                ->modal('createGroupModal')
                ->method('createGroup'),

            Button::make('Назад к этапам')
                ->icon('arrow-left')
                ->method('backToStages'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $tabs = [];

        foreach ($this->groups as $group) {
            $tabs[$group->name] = $this->buildGroupTab($group);
        }

        // Если групп нет, показываем заглушку
//        if (empty($tabs)) {
//            $tabs['Нет групп'] = Layout::view('orchid.empty-groups', [
//                'message' => 'Группы еще не созданы. Добавьте первую группу используя кнопку выше.'
//            ]);
//        }

        return [
            Layout::modal('createGroupModal', [
                Layout::rows([
                    Input::make('group.name')
                        ->title('Название группы')
                        ->required()
                        ->help('Укажите название группы (например: Группа A)'),

                    Input::make('group.order')
                        ->title('Порядок')
                        ->type('number')
                        ->min(1)
                        ->value(1)
                        ->help('Порядковый номер группы для сортировки'),
                ])
            ])
                ->title('Создать новую группу')
                ->applyButton('Создать')
                ->closeButton('Отмена'),

            Layout::modal('editGroupModal', [
                Layout::rows([
                    Input::make('group.id')
                        ->type('hidden'),

                    Input::make('group.name')
                        ->title('Название группы')
                        ->required()
                        ->help('Укажите название группы'),

                    Input::make('group.order')
                        ->title('Порядок')
                        ->type('number')
                        ->min(1)
                        ->help('Порядковый номер группы для сортировки'),
                ])
            ])
                ->title('Редактировать группу')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->async('asyncGetGroupData'),

            Layout::modal('addTeamModal', [
                Layout::rows([
                    Input::make('group_id')
                        ->type('hidden'),

                    Select::make('application_id')
                        ->title('Выберите команду')
                        ->empty('Не выбрано')
                        ->fromQuery(
                            TournamentApplication::with('team')
                                ->where('tournament_id', $this->tournament->id)
                                ->where('status', 'approved')
                                ->notInStageGroups($this->stage->id), // Используем scope
                            'team.name'
                        )
                        ->required()
                        ->help('Выберите команду для добавления в группу. Показываются только команды, не добавленные в другие группы этого этапа.'),
                ])
            ])
                ->title('Добавить команду в группу')
                ->applyButton('Добавить')
                ->closeButton('Отмена')
                ->async('asyncGetGroupData'),

            Layout::tabs($tabs),
        ];
    }

    /**
     * Строит layout для вкладки группы
     */
    private function buildGroupTab(StageGroup $group)//: Layout
    {

        $teamsCount = $group->teams->count();

        return Layout::columns([
            // Левая колонка - команды в группе
            Layout::table('groupsData.' . $group->id, [
                TD::make('name', 'Команда')
                    ->sort()
                    ->render(function (Team $team) {
                        return $team->name;
                    }),

                TD::make('actions', 'Действия')
                    ->alignRight()
                    ->render(function (Team $team) use ($group) {
                        // Находим application_id для этой команды в этой группе
                        $application = TournamentApplication::where('team_id', $team->id)
                            ->where('tournament_id', $this->tournament->id)
                            ->where('status', 'approved')
                            ->first();

                        if (!$application) {
                            return 'Нет заявки';
                        }

                        return Button::make('Убрать')
                            ->icon('trash')
                            ->type(Color::DANGER)
                            ->confirm('Вы уверены, что хотите убрать команду из группы?')
                            ->method('removeTeamFromGroup', [
                                'group_id' => $group->id,
                                'application_id' => $application->id,
                            ]);
                    }),
            ])->title('Команды в группе (' . $teamsCount . ')'),

            // Правая колонка - управление группой
            Layout::rows([
                \Orchid\Screen\Fields\Label::make('info')
                    ->title('Управление группой')
                    ->value($group->name),

                Group::make([
                    ModalToggle::make('Добавить команду')
                        ->icon('plus')
                        ->type(Color::SUCCESS)
                        ->modal('addTeamModal')
                        ->method('addTeamToGroup')
                        ->asyncParameters([
                            'group_id' => $group->id,
                        ]),

                    ModalToggle::make('Редактировать')
                        ->icon('pencil')
                        ->type(Color::PRIMARY)
                        ->modal('editGroupModal')
                        ->method('updateGroup')
                        ->asyncParameters([
                            'group_id' => $group->id,
                        ]),

                    Button::make('Удалить группу')
                        ->icon('trash')
                        ->type(Color::DANGER)
                        ->confirm('Вы уверены, что хотите удалить группу? Все команды будут убраны из группы.')
                        ->method('deleteGroup', [
                            'group_id' => $group->id,
                        ]),
                ])->autoWidth(),
            ]),
        ]);
    }

    /**
     * Async метод для получения данных модального окна
     */
//    public function asyncGetGroupData(int $group_id): array
//    {
//        return [
//            'group_id' => $group_id,
//        ];
//    }
    public function asyncGetGroupData(int $group_id): array
    {
        $group = StageGroup::findOrFail($group_id);

        return [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'order' => $group->order,
            ],
            'group_id' => $group->id,
        ];
    }

    /**
     * Создание новой группы
     */
    public function createGroup(TournamentStage $stage, Request $request)
    {
        $request->validate([
            'group.name' => 'required|string|max:255',
            'group.order' => 'required|integer|min:1',
        ]);

        StageGroup::create([
            'stage_id' => $stage->id,
            'name' => $request->input('group.name'),
            'order' => $request->input('group.order'),
        ]);

        Toast::info('Группа успешно создана');
    }

    /**
     * Добавление команды в группу
     */
    public function addTeamToGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:stage_groups,id',
            'application_id' => 'required|exists:tournament_applications,id',
        ]);

        // Проверяем, что заявка approved и принадлежит текущему турниру
        $application = TournamentApplication::where('id', $request->input('application_id'))
            ->where('tournament_id', $this->tournament->id)
            ->where('status', 'approved')
            ->firstOrFail();

        // Добавляем связь через GroupTeam с указанием application_id
        GroupTeam::create([
            'group_id' => $request->input('group_id'),
            'application_id' => $request->input('application_id'),
        ]);

        Toast::info('Команда добавлена в группу');
    }

    /**
     * Удаление команды из группы
     */
    public function removeTeamFromGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:stage_groups,id',
            'application_id' => 'required|exists:tournament_applications,id',
        ]);

        GroupTeam::where('group_id', $request->input('group_id'))
            ->where('application_id', $request->input('application_id'))
            ->delete();

        Toast::info('Команда убрана из группы');
    }

    /**
     * Обновление группы
     */
//    public function updateGroup(StageGroup $group, Request $request)
//    {
//        $request->validate([
//            'group.name' => 'required|string|max:255',
//            'group.order' => 'required|integer|min:1',
//        ]);
//
//        $group->update($request->input('group'));
//
//        Toast::info('Группа обновлена');
//    }
    /**
     * Обновление группы
     */
    public function updateGroup(Request $request)
    {
        try {
            $request->validate([
                'group.id' => 'required|exists:stage_groups,id',
                'group.name' => 'required|string|max:255',
                'group.order' => 'required|integer|min:1',
            ]);

            $group = StageGroup::findOrFail($request->input('group.id'));
            $group->update($request->input('group'));

            Toast::info('Группа успешно обновлена');

        } catch (\Exception $e) {
            Toast::error('Ошибка при обновлении группы: ' . $e->getMessage());
        }
    }

    /**
     * Удаление группы
     */
    public function deleteGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:stage_groups,id',
        ]);

        $group = StageGroup::findOrFail($request->input('group_id'));

        // Удаляем все связи с командами
        GroupTeam::where('group_id', $group->id)->delete();

        // Удаляем группу
        $group->delete();

        Toast::info('Группа удалена');
    }

    /**
     * Возврат к списку этапов
     */
    public function backToStages(Tournament $tournament)
    {
        return redirect()->route('platform.tournaments.edit', $tournament);
    }
}

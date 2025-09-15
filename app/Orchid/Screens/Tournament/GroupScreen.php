<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\StageGroup;
use App\Models\TournamentStage;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

class GroupScreen extends Screen
{
    public $groups;
    //public $stage;

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
        return 'GroupScreen';
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

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {

        foreach ($this->groups as $group)
        {
            $tabs = [
                $group->name => Layout::rows([
                    Input::make('group.name')
                        ->title('Название группы')
                        ->required(),

                ]),
            ];
        }

        return [

                Layout::tabs([
                    $tabs
                ])



//            Layout::tabs([
//                'Основное' => Layout::rows([
//                    Input::make('group.name')
//                        ->title('Название группы')
//                        ->required(),
//
//                    Input::make('group.team_count')
//                        ->title('Количество команд')
//                        ->type('number')
//                        ->min(2),
//                ]),
//
//                'Команды' => Layout::rows([
//                    Matrix::make('group.teams')
//                        ->title('Распределение команд')
//                        ->columns([
//                            'Команда' => 'team.name',
//                            'Позиция' => 'pivot.position'
//                        ])
//                        ->fields([
//                            'pivot.position' => Input::make()
//                                ->type('number')
//                                ->min(1)
//                        ])
//                ])
//            ])
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

}

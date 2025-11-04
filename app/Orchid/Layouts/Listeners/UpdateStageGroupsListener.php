<?php

namespace App\Orchid\Layouts\Listeners;

use App\Models\Tournament;
use App\Models\TournamentStage;
use App\Models\StageGroup;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Repository;
use Illuminate\Http\Request;

class UpdateStageGroupsListener extends Listener
{
    /**
     * Поля, которые отслеживаем
     */
    protected $targets = [
        'tournament_id',
        'stage_id',
        'group_id',
    ];

    /**
     * Обработка изменений!
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        // Получаем текущие значения
        $tournamentId = $request->get('tournament_id');
        $stageId = $request->get('stage_id');
        $groupId = $request->get('group_id');

        // Обновляем все значения в репозитории
        $repository->set('tournament_id', $tournamentId);
        $repository->set('stage_id', $stageId);
        $repository->set('group_id', $groupId);

        return $repository;
    }

    /**
     * Генерация layout'ов
     */
    protected function layouts(): array
    {
        $tournamentId = $this->query->get('tournament_id');
        $stageId = $this->query->get('stage_id');
        $groupId = $this->query->get('group_id');

        $hasAllParams = !empty($tournamentId) && !empty($stageId) && !empty($groupId);

        return [
            Layout::rows([
                Group::make([
                    Select::make('tournament_id')
                        ->title('Турнир')
                        ->empty('Выберите турнир')
                        ->fromModel(Tournament::class, 'name')
                        ->value($tournamentId)
                        ->required()

                        ->help('Выберите турнир для управления играми'),

                    Select::make('stage_id')
                        ->title('Этап')
                        ->empty('Выберите этап')
                        ->fromQuery(
                            TournamentStage::where('tournament_id', $tournamentId),
                            'name',
                            'id'
                        )
                        ->value($stageId)
                        ->canSee((bool)$tournamentId)
                        ->required()
                        ->help('Выберите этап турнира'),

                    Select::make('group_id')
                        ->title('Группа')
                        ->empty('Выберите группу')
                        ->fromQuery(
                            StageGroup::where('stage_id', $stageId),
                            'name',
                            'id'
                        )
                        ->value($groupId)
                        ->canSee((bool)$stageId)
                        ->required()
                        ->help('Выберите группу этапа'),
                ])
                    ->widthColumns('2fr 1fr 1fr'),
                Link::make('Расписание игр')
                    ->route('platform.tournament.games.list', [
                        'tournament' => (int)$tournamentId,
                        'stage' => (int)$stageId,
                        'group' => (int)$groupId,
                    ])
                    ->canSee($hasAllParams)
                    ->type(Color::PRIMARY)
                    //->class('btn btn-primary')
                    ,
                //->help('Перейти к управлению играми выбранной группы'),
            ]),
        ];
    }
}

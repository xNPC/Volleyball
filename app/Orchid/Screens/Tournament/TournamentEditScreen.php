<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use App\Models\TournamentStage;
use App\Orchid\Layouts\Tournament\StageListTable;
use App\Orchid\Layouts\Tournament\StagesLayout;
use App\Orchid\Layouts\Tournament\TournamentEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class TournamentEditScreen extends Screen
{
    public $tournament;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Tournament $tournament): array
    {
        return [
            'tournament' => $tournament->load('organization'),
            'organizations' => Organization::all(),
            'stages' => $tournament->stages,
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить этап')
                ->modal('createOrUpdateStage')
                ->method('createOrUpdateStage')
                ->icon('plus')
                ->canSee($this->tournament->exists)

//            Button::make('Удалить')
//                ->icon('trash')
//                ->method('remove')
//                ->canSee($this->tournament->exists)
        ];
    }

    public function name(): ?string
    {
        return $this->tournament->exists ? 'Редактирование турнира' : 'Создание турнира';
    }

    public function layout(): array
    {
        $columns = [
            TournamentEditLayout::class
        ];

        if ($this->tournament->exists) {
            $columns[] = StageListTable::class;
        }

        return [
            Layout::modal('createOrUpdateStage', [
                StagesLayout::class
            ])
                ->title('Добавить этап')
                ->applyButton('Сохранить')
                ->async('asyncGetStage'),

            Layout::columns($columns)
        ];

    }

    public function asyncGetStage(TournamentStage $stage): array
    {
        return [
            'stage' => $stage
        ];
    }

    public function save(Tournament $tournament, Request $request)
    {
        $validated = $request->validate([
            'tournament.name' => 'required|string|max:255',
            'tournament.organization_id' => 'required|exists:organizations,id',
            'tournament.description' => 'string',
            'tournament.start_date' => 'required|date',
            'tournament.end_date' => 'required|date|after_or_equal:tournament.start_date',
            'tournament.status' => 'required|in:planned,ongoing,completed'
        ]);

        $tournament->fill($validated['tournament'])->save();

        Toast::info('Успешно сохранено');

        //return redirect()->route('platform.tournaments.list');
    }

    public function remove(Tournament $tournament)
    {
        $tournament->delete();
        return redirect()->route('platform.tournament.list');
    }

    public function createOrUpdateStage(Request $request)
    {
        $stageId = $request->input('stage.id');

        $validated = $request->validate([
            'stage.name' => 'required|string|max:255',
            'stage.stage_type' => 'required|in:group,playoff,qualification',
            'stage.order' => 'required|integer|min:1',
            'stage.start_date' => 'required|date',
            'stage.end_date' => 'required|date|after_or_equal:stage.start_date',
        ]);

        TournamentStage::updateOrCreate([
            'id' => $stageId,
            'tournament_id' => $this->tournament->id
        ],
            $validated['stage']
        );

        Toast::info('Успешно сохранено');

    }

    public function removeStage(TournamentStage $stage)
    {
        $stage->delete();

        Toast::info('Этап успешно удален');
        //return redirect()->route('platform.tournament.list');
    }

}

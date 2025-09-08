<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use App\Models\TournamentStage;
use App\Orchid\Layouts\Tournament\StageListLayout;
use App\Orchid\Layouts\Tournament\StageListTable;
use App\Orchid\Layouts\Tournament\StagesLayout;
use App\Orchid\Layouts\Tournament\TournamentEditLayout;
use Illuminate\Http\Request;
use Orchid\Platform\Dashboard;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Color;
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
//            Button::make('Добавить этап')
//                ->modal('createStage')
//                ->icon('plus')
//                ->method('createStage')
//                ->canSee($this->tournament->exists),

            ModalToggle::make('Добавить этап')
                ->modal('createOrUpdateStage')
                ->method('createOrUpdateStage')
                ->icon('plus')

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

    public function asyncGetStage(TournamentStage $stage)
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

        return redirect()->route('platform.tournaments.list');
    }

    public function remove(Tournament $tournament)
    {
        $tournament->delete();
        return redirect()->route('platform.tournament.list');
    }

    public function createOrUpdateStage(Request $request)
    {
        Toast::info('ok');

        dd($request->all());
    }

    /*public function save(Tournament $tournament, Request $request)
    {
        Toast::info($request->input('tournament')['organization_id']);

        $data = $request->validate([
            'tournament.name' => 'required|string|max:255',
            'tournament.stages' => 'array',
        ]);

        $tournament->fill($data['tournament'])->save();

        // Обработка этапов
        $tournament->stages()->delete();

        foreach ($request->input('tournament.stages', []) as $stageData) {
            $tournament->stages()->create($stageData);
        }

        return redirect()->route('platform.tournament.list');
    }*/

}

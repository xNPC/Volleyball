<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use App\Models\TournamentStage;
use App\Orchid\Layouts\Tournament\StageListTable;
use Illuminate\Http\Request;
use Orchid\Platform\Dashboard;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Menu;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
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
            //'stages' => $tournament->stages,
            'stages' => TournamentStage::sorted()->get()
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
            Button::make('Сохранить')
                ->icon('check')
                ->method('save'),

            Button::make('Удалить')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->tournament->exists)
        ];
    }

    public function name(): ?string
    {
        return $this->tournament->exists ? 'Редактирование турнира' : 'Создание турнира';
    }

    public function layout(): array
    {
        $tabs = [
            'Основное' => Layout::rows([
                Input::make('tournament.name')
                    ->title('Название турнира')
                    ->required(),

                Select::make('tournament.organization_id')
                    ->fromQuery(Organization::query(), 'name')
                    ->title('Организация')
                    ->required()
                    ->help('Выберите организацию, проводящую турнир'),

                TextArea::make('tournament.description')
                    ->title('Описание')
                    ->rows(3),

                DateTimer::make('tournament.start_date')
                    ->title('Дата начала')
                    ->required()
                    ->format('Y-m-d'),

                DateTimer::make('tournament.end_date')
                    ->title('Дата окончания')
                    ->required()
                    ->format('Y-m-d'),

                Select::make('tournament.status')
                    ->options([
                        'planned' => 'Запланирован',
                        'ongoing' => 'В процессе',
                        'completed' => 'Завершен'
                    ])
                    ->title('Статус')
                    ->required()
            ])
        ];

        if ($this->tournament->exists)
            $tabs['Этапы'] = StageListTable::class;

        return [
            Layout::tabs($tabs)
                ->activeTab('Основное')
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

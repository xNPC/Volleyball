<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use App\Orchid\Layouts\Tournament\StagesLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

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
            'tournament' => $tournament->load('stages')
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

    public function name(): ?string
    {
        return $this->tournament->exists ? 'Редактирование турнира' : 'Создание турнира';
    }

    public function layout(): array
    {
        return [
            Layout::tabs([
                'Основное' => Layout::rows([
                    Select::make('tournament.organization_id')
                        ->fromModel(Organization::class, 'name')
                        ->title('Организация')
                        ->required(),

                    Input::make('tournament.name')
                        ->title('Название')
                        ->required(),

                    DateRange::make('tournament.dates')
                        ->title('Даты проведения')
                        ->required(),

                    Select::make('tournament.status')
                        ->options([
                            'planned' => 'Запланирован',
                            'ongoing' => 'В процессе',
                            'completed' => 'Завершен'
                        ])
                        ->title('Статус'),
                ]),

                'Этапы' => Layout::rows([
                    StagesLayout::class
                ]),
            ])
        ];
    }

    public function save(Tournament $tournament, Request $request)
    {
        $data = $request->validate([
            'tournament.name' => 'required|string|max:255',
            'tournament.stages' => 'array'
        ]);

        $tournament->fill($data['tournament'])->save();

        // Обработка этапов
        $tournament->stages()->delete();

        foreach ($request->input('tournament.stages', []) as $stageData) {
            $tournament->stages()->create($stageData);
        }

        return redirect()->route('platform.tournament.list');
    }

}

<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\TournamentStage;
use App\Models\Tournament;
use App\Models\StageGroup;
use App\Models\PlayoffConfig;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupPlayoffConfigScreen extends Screen
{
    public $stage;
    public $tournament;
    public $group;
    public $config;

    public function query(Tournament $tournament, TournamentStage $stage, StageGroup $group): iterable
    {
        $this->tournament = $tournament;
        $this->stage = $stage;
        $this->group = $group;
        $this->config = PlayoffConfig::where('stage_id', $stage->id)
            ->where('group_id', $group->id)
            ->first();

        return [
            'stage' => $stage,
            'tournament' => $tournament,
            'group' => $group,
            'config' => $this->config,
            'teams_count' => $group->teams->count(),
        ];
    }

    public function name(): ?string
    {
        return "Настройка плейофф: {$this->group->name}";
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')->icon('check')->method('save'),
            Link::make('Назад')->icon('arrow-left')->route('platform.tournament.groups', [
                'tournament' => $this->tournament->id,
                'stage' => $this->stage->id,
            ]),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('config.total_teams')
                    ->title('Количество команд')
                    ->type('number')
                    ->value($this->group->teams->count())
                    ->readonly()
                    ->help('Всего команд в группе: ' . $this->group->teams->count()),

                Select::make('config.bye_positions')
                    ->title('Команды, проходящие в следующий раунд без игры (BYE)')
                    ->options($this->getTeamOptions())
                    ->multiple()
                    ->value($this->config->bye_positions ?? [])
                    ->help('Выберите команды, которые сразу проходят в следующий раунд'),

                CheckBox::make('config.reverse_seeding')
                    ->title('Обратный посев')
                    ->placeholder('Поменять местами верхнюю и нижнюю половину')
                    ->value($this->config->reverse_seeding ?? false),
            ]),
        ];
    }

    private function getTeamOptions(): array
    {
        $options = [];
        foreach ($this->group->teams as $application) {
            $position = $application->pivot->position ?? '?';
            $options[$position] = "Позиция {$position}: {$application->team->name}";
        }
        ksort($options);
        return $options;
    }

    public function save(Request $request)
    {
        $data = $request->input('config', []);

        $configData = [
            'stage_id' => $this->stage->id,
            'group_id' => $this->group->id,
            'total_teams' => $this->group->teams->count(),
            'bye_positions' => array_map('intval', $data['bye_positions'] ?? []),
            'reverse_seeding' => isset($data['reverse_seeding']) && $data['reverse_seeding'] === 'on',
        ];

        try {
            if ($this->config) {
                $this->config->update($configData);
            } else {
                PlayoffConfig::create($configData);
            }
            Toast::success('Настройки сохранены!');
        } catch (\Exception $e) {
            Toast::error('Ошибка: ' . $e->getMessage());
        }

        return redirect()->route('platform.tournament.groups', [
            'tournament' => $this->tournament->id,
            'stage' => $this->stage->id,
        ]);
    }
}

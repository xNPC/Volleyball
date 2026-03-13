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
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GroupPlayoffConfigScreen extends Screen
{
    public $stage;
    public $tournament;
    public $group;
    public $config;

    /**
     * Query data.
     */
    public function query(Tournament $tournament, TournamentStage $stage, StageGroup $group): iterable
    {
        $this->tournament = $tournament;
        $this->stage = $stage;
        $this->group = $group;

        // Получаем конфигурацию для этой группы
        $this->config = PlayoffConfig::where('stage_id', $stage->id)
            ->where('group_id', $group->id)
            ->first();

        // Подготавливаем данные для отображения в форме
        $configForDisplay = $this->prepareConfigForDisplay($this->config);

        return [
            'stage' => $stage,
            'tournament' => $tournament,
            'group' => $group,
            'config' => $configForDisplay,
            'teams_count' => $group->teams->count(),
        ];
    }

    /**
     * Подготавливает конфигурацию для отображения в форме
     */
    private function prepareConfigForDisplay($config)
    {
        if (!$config) {
            return [
                'format_type' => 'single_elimination',
                'seeding_type' => 'standard',
                'reverse_seeding' => false,
                'special_byes' => [],
                'rounds_config' => $this->generateDefaultRoundsConfigForDisplay($this->group->teams->count()),
            ];
        }

        // Проверяем, является ли $config объектом модели или массивом
        if (is_object($config) && method_exists($config, 'toArray')) {
            $configArray = $config->toArray();
        } else {
            $configArray = (array)$config;
        }

        // Подготавливаем special_byes для Matrix
        $specialByes = [];
        if (!empty($configArray['seeding_rules']['special_byes'])) {
            foreach ($configArray['seeding_rules']['special_byes'] as $bye) {
                $specialByes[] = [
                    'position' => (string)($bye['position'] ?? ''),
                    'round' => (string)($bye['round'] ?? ''),
                    'description' => $bye['description'] ?? '',
                ];
            }
        }

        // Подготавливаем rounds_config для Matrix
        $roundsConfig = [];
        if (!empty($configArray['rounds_config'])) {
            foreach ($configArray['rounds_config'] as $roundNum => $round) {
                $roundsConfig[] = [
                    'name' => $round['name'] ?? $this->getRoundNameByTeams($this->group->teams->count(), $roundNum),
                    'best_of' => (string)($round['best_of'] ?? '1'),
                    'tie_breaker' => $round['tie_breaker'] ?? 'golden_set',
                ];
            }
        } else {
            $roundsConfig = $this->generateDefaultRoundsConfigForDisplay($this->group->teams->count());
        }

        return [
            'format_type' => $configArray['format_type'] ?? 'single_elimination',
            'seeding_type' => $configArray['seeding_rules']['type'] ?? 'standard',
            'reverse_seeding' => isset($configArray['seeding_rules']['reverse']) && $configArray['seeding_rules']['reverse'] ? 'on' : null,
            'special_byes' => $specialByes,
            'rounds_config' => $roundsConfig,
        ];
    }

    /**
     * Генерирует стандартную конфигурацию раундов для отображения
     */
    private function generateDefaultRoundsConfigForDisplay(int $teamCount): array
    {
        $rounds = ceil(log($teamCount, 2));
        $config = [];
        $currentTeams = $teamCount;

        for ($i = 1; $i <= $rounds; $i++) {
            $config[] = [
                'name' => $this->getRoundName($currentTeams),
                'best_of' => ($i == $rounds) ? '3' : '1',
                'tie_breaker' => 'golden_set',
            ];
            $currentTeams = floor($currentTeams / 2);
        }

        return $config;
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return "Настройка плейофф: {$this->group->name}";
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('check')
                ->method('save'),

            Button::make('Применить стандартные')
                ->icon('refresh')
                ->method('applyStandard'),

            Link::make('Назад к этапу')
                ->icon('arrow-left')
                ->route('platform.tournament.groups', [
                    'tournament' => $this->tournament->id,
                    'stage' => $this->stage->id,
                ]),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        // Получаем данные для отображения
        $configForDisplay = $this->prepareConfigForDisplay($this->config);

        return [
            Layout::tabs([
                'Основные настройки' => Layout::rows([
                    Select::make('config.format_type')
                        ->title('Формат плейофф')
                        ->options([
                            'single_elimination' => 'Олимпийская система (на вылет)',
                            'double_elimination' => 'Двойная система (два шанса)',
                        ])
                        ->value($configForDisplay['format_type'])
                        ->required()
                        ->help('Выберите формат проведения'),

                    Input::make('config.total_teams')
                        ->title('Количество команд')
                        ->type('number')
                        ->value($this->group->teams->count())
                        ->readonly()
                        ->help('Команд в группе: ' . $this->group->teams->count()),

                    Select::make('config.seeding_type')
                        ->title('Тип посева')
                        ->options([
                            'standard' => 'Стандартный (1-последний, 2-предпоследний...)',
                            'groups' => 'По позициям в группах',
                            'random' => 'Случайный',
                        ])
                        ->value($configForDisplay['seeding_type'])
                        ->help('Как распределить команды в сетке'),
                ]),

                'Правила посева' => Layout::rows([
                    Matrix::make('config.special_byes')
                        ->title('Специальные выходы (BYE)')
                        ->columns([
                            'Позиция' => 'position',
                            'Раунд' => 'round',
                            'Описание' => 'description',
                        ])
                        ->fields([
                            'position' => Input::make()->type('number')->min(1)->max($this->group->teams->count()),
                            'round' => Select::make()->options([
                                1 => '1/4 финала',
                                2 => '1/2 финала',
                                3 => 'Финал',
                            ]),
                            'description' => Input::make()->placeholder('Например: Победитель группы А'),
                        ])
                        ->value($configForDisplay['special_byes'])
                        ->help('Команды с указанных позиций сразу проходят в указанный раунд'),

                    CheckBox::make('config.reverse_seeding')
                        ->title('Обратный посев')
                        ->placeholder('Поменять местами верхнюю и нижнюю половину')
                        ->value($configForDisplay['reverse_seeding'] === 'on')
                        ->help('Использовать обратный порядок при распределении'),
                ]),

                'Формат матчей' => Layout::rows([
                    Matrix::make('config.rounds_config')
                        ->title('Настройка раундов')
                        ->columns([
                            'Раунд' => 'name',
                            'До побед' => 'best_of',
                            'Тай-брейк' => 'tie_breaker',
                        ])
                        ->fields([
                            'name' => Input::make()->readonly(),
                            'best_of' => Select::make()->options([
                                1 => 'Один матч',
                                3 => 'До 2 побед',
                                5 => 'До 3 побед',
                            ]),
                            'tie_breaker' => Select::make()->options([
                                'golden_set' => 'Золотой сет',
                                'extra_game' => 'Дополнительная игра',
                                'points' => 'По очкам',
                            ]),
                        ])
                        ->value($configForDisplay['rounds_config'])
                        ->help('Настройте каждый раунд'),
                ]),

                'Предпросмотр' => Layout::view('orchid.group-playoff-preview', [
                    'config' => $this->config,
                    'group' => $this->group,
                    'teams' => $this->group->teams,
                ]),
            ]),
        ];
    }

    /**
     * Сохранение настроек
     */
    public function save(Request $request)
    {
        $data = $request->input('config', []);

        \Log::info('Playoff config save raw data:', $data);

        $request->validate([
            'config.format_type' => 'required|string',
        ]);

        // Обрабатываем special_byes
        $specialByes = [];
        if (isset($data['special_byes']) && is_array($data['special_byes'])) {
            foreach ($data['special_byes'] as $bye) {
                if (is_array($bye) && !empty($bye['position'])) {
                    $specialByes[] = [
                        'position' => (int)$bye['position'],
                        'round' => (int)($bye['round'] ?? 1),
                        'description' => $bye['description'] ?? '',
                    ];
                }
            }
        }

        // Обрабатываем rounds_config
        $roundsConfig = [];
        if (isset($data['rounds_config']) && is_array($data['rounds_config'])) {
            foreach ($data['rounds_config'] as $index => $round) {
                if (is_array($round)) {
                    $roundsConfig[$index + 1] = [
                        'name' => $round['name'] ?? $this->getRoundNameByTeams($this->group->teams->count(), $index + 1),
                        'best_of' => (int)($round['best_of'] ?? 1),
                        'tie_breaker' => $round['tie_breaker'] ?? 'golden_set',
                    ];
                }
            }
        }

        // Если rounds_config пуст, генерируем стандартный
        if (empty($roundsConfig)) {
            $roundsConfig = $this->generateDefaultRoundsConfig($this->group->teams->count());
        }

        // Подготавливаем данные
        $configData = [
            'stage_id' => $this->stage->id,
            'group_id' => $this->group->id,
            'format_type' => $data['format_type'] ?? 'single_elimination',
            'total_teams' => $this->group->teams->count(),
            'seeding_rules' => [
                'type' => $data['seeding_type'] ?? 'standard',
                'reverse' => isset($data['reverse_seeding']) && $data['reverse_seeding'] === 'on',
                'special_byes' => $specialByes,
            ],
            'rounds_config' => $roundsConfig,
            'bracket_structure' => $this->generateStructure($this->group->teams->count()),
            'matchups' => $this->generateMatchups($this->group->teams->count(), $data),
        ];

        \Log::info('Playoff config processed data:', $configData);

        try {
            // Ищем существующую конфигурацию в БД
            $existingConfig = PlayoffConfig::where('stage_id', $this->stage->id)
                ->where('group_id', $this->group->id)
                ->first();

            if ($existingConfig) {
                // Обновляем существующую
                $existingConfig->update($configData);
                Toast::success('Настройки обновлены!');
            } else {
                // Создаем новую
                PlayoffConfig::create($configData);
                Toast::success('Настройки созданы!');
            }

            return redirect()->route('platform.tournament.groups.playoff-config', [
                'tournament' => $this->tournament->id,
                'stage' => $this->stage->id,
                'group' => $this->group->id,
            ]);

        } catch (\Exception $e) {
            Toast::error('Ошибка: ' . $e->getMessage());
            \Log::error('Playoff config save error: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Применить стандартные настройки
     */
    public function applyStandard()
    {
        $teamsCount = $this->group->teams->count();
        $rounds = ceil(log($teamsCount, 2));

        $roundsConfig = [];
        $currentTeams = $teamsCount;

        for ($i = 1; $i <= $rounds; $i++) {
            $roundsConfig[] = [
                'name' => $this->getRoundName($currentTeams),
                'best_of' => ($i == $rounds) ? 3 : 1,
                'tie_breaker' => 'golden_set',
            ];
            $currentTeams = floor($currentTeams / 2);
        }

        return redirect()->back()->withInput([
            'config' => [
                'format_type' => 'single_elimination',
                'seeding_type' => 'standard',
                'reverse_seeding' => false,
                'special_byes' => [],
                'rounds_config' => $roundsConfig,
            ]
        ]);
    }

    private function processByes($byes)
    {
        $result = [];
        if (isset($byes[0]) && is_array($byes[0])) {
            foreach ($byes as $bye) {
                if (!empty($bye['position'])) {
                    $result[] = [
                        'position' => (int)$bye['position'],
                        'round' => (int)$bye['round'],
                        'description' => $bye['description'] ?? '',
                    ];
                }
            }
        }
        return $result;
    }

    private function processRounds($rounds)
    {
        $result = [];
        if (isset($rounds[0]) && is_array($rounds[0])) {
            foreach ($rounds as $index => $round) {
                $result[$index + 1] = [
                    'name' => $round['name'] ?? 'Раунд ' . ($index + 1),
                    'best_of' => (int)($round['best_of'] ?? 1),
                    'tie_breaker' => $round['tie_breaker'] ?? 'golden_set',
                ];
            }
        }
        return $result;
    }

    private function generateDefaultRoundsConfig(int $teamCount): array
    {
        $rounds = ceil(log($teamCount, 2));
        $config = [];
        $currentTeams = $teamCount;

        for ($i = 1; $i <= $rounds; $i++) {
            $config[$i] = [
                'name' => $this->getRoundName($currentTeams),
                'best_of' => ($i == $rounds) ? 3 : 1,
                'tie_breaker' => 'golden_set',
            ];
            $currentTeams = floor($currentTeams / 2);
        }

        return $config;
    }

    private function generateStructure($teamCount)
    {
        $structure = [];
        $rounds = ceil(log($teamCount, 2));
        $currentTeams = $teamCount;

        for ($i = 1; $i <= $rounds; $i++) {
            $structure[] = [
                'round' => $i,
                'name' => $this->getRoundName($currentTeams),
                'teams' => $currentTeams,
                'matches' => $currentTeams / 2,
            ];
            $currentTeams = floor($currentTeams / 2);
        }

        return $structure;
    }

    private function generateMatchups($teamCount, $data)
    {
        $matchups = [];
        $rounds = ceil(log($teamCount, 2));

        // Обрабатываем special byes
        $specialByes = [];
        if (isset($data['special_byes']) && is_array($data['special_byes'])) {
            foreach ($data['special_byes'] as $bye) {
                if (is_array($bye) && !empty($bye['position'])) {
                    $specialByes[] = [
                        'position' => (int)$bye['position'],
                        'round' => (int)($bye['round'] ?? 1),
                    ];
                }
            }
        }

        // Первый раунд
        $firstRound = [];
        for ($i = 0; $i < $teamCount / 2; $i++) {
            $homePos = $i + 1;
            $awayPos = $teamCount - $i;

            // Проверяем специальные выходы
            $homeBye = null;
            $awayBye = null;

            foreach ($specialByes as $bye) {
                if ($bye['position'] == $homePos) $homeBye = $bye['round'];
                if ($bye['position'] == $awayPos) $awayBye = $bye['round'];
            }

            $firstRound[] = [
                'home' => $homePos,
                'away' => $awayPos,
                'home_bye' => $homeBye,
                'away_bye' => $awayBye,
            ];
        }

        // Обратный посев
        if (isset($data['reverse_seeding']) && $data['reverse_seeding'] === 'on') {
            $firstRound = array_reverse($firstRound);
        }

        $matchups[1] = $firstRound;

        // Последующие раунды
        $matchesCount = count($firstRound);
        $currentRound = 1;

        while ($matchesCount > 1) {
            $nextRound = [];
            for ($i = 0; $i < $matchesCount / 2; $i++) {
                $nextRound[] = [
                    'home_from' => $i * 2 + 1,
                    'away_from' => $i * 2 + 2,
                ];
            }
            $matchups[$currentRound + 1] = $nextRound;
            $matchesCount = count($nextRound);
            $currentRound++;
        }

        return $matchups;
    }

    private function getRoundName($teams)
    {
        $names = [
            2 => 'Финал',
            4 => '1/2 финала',
            8 => '1/4 финала',
            16 => '1/8 финала',
            32 => '1/16 финала',
        ];

        return $names[$teams] ?? "1/{$teams} финала";
    }

    private function getRoundNameByTeams(int $teamCount, int $roundNumber): string
    {
        $teamsInRound = $teamCount / pow(2, $roundNumber - 1);
        return $this->getRoundName($teamsInRound);
    }
}

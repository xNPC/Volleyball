<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\TournamentStage;
use App\Models\Tournament;
use App\Models\StageGroup;
use App\Services\PlayoffConfigurator;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlayoffConfigScreen extends Screen
{
    public $stage;
    public $tournament;
    public $config;

    /**
     * Query data.
     */
    public function query(Tournament $tournament, TournamentStage $stage): iterable
    {
        $this->tournament = $tournament;
        $this->stage = $stage;
        $this->config = $stage->playoffConfig ?? null;

        // Загружаем группы для этого этапа
        $groups = $stage->groups()->with('teams.team')->get();

        return [
            'stage' => $stage,
            'tournament' => $tournament,
            'config' => $this->getConfigForDisplay(), // Используем преобразованные данные
            'groups' => $groups,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return "Настройка плейофф: {$this->stage->name}";
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить настройки')
                ->icon('check')
                ->method('save'),

            Button::make('Назад')
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

        $debugLayout = Layout::rows([
            \Orchid\Screen\Fields\Label::make('debug')
                ->title('Отладочная информация')
                ->value(json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
        ]);

        return [
            // Основные настройки
            Layout::tabs([
                'Основные настройки' => Layout::rows([
                    Select::make('config.format_type')
                        ->title('Тип сетки')
                        ->options([
                            'single_elimination' => 'Олимпийская система (на вылет)',
                            'double_elimination' => 'Двойная система (два шанса)',
                            'custom' => 'Пользовательская',
                        ])
                        ->required()
                        ->help('Выберите формат проведения плейофф'),

                    Input::make('config.total_teams')
                        ->title('Количество команд')
                        ->type('number')
                        ->min(2)
                        ->max(64)
                        ->required()
                        ->help('Общее количество команд, участвующих в плейофф'),

                    Select::make('config.source_group_id')
                        ->title('Источник команд')
                        ->options($this->getGroupsOptions())
                        ->help('Из какой группы берутся команды (если не выбрано - из всех групп)'),

                    TextArea::make('config.description')
                        ->title('Описание формата')
                        ->rows(3)
                        ->help('Краткое описание схемы плейофф для администраторов'),
                ]),

                'Правила посева' => Layout::rows([
                    Select::make('config.seeding_type')
                        ->title('Тип посева')
                        ->options([
                            'standard' => 'Стандартный (1-последний, 2-предпоследний...)',
                            'group_winners' => 'Победители групп',
                            'random' => 'Случайный',
                            'manual' => 'Ручной',
                        ])
                        ->value($this->config['seeding_type'] ?? 'standard')
                        ->help('Как распределять команды по сетке'),

                    Matrix::make('config.special_byes')
                        ->title('Специальные выходы (BYE)')
                        ->columns([
                            'Позиция' => 'position',
                            'Раунд' => 'round',
                            'Описание' => 'description',
                        ])
                        ->fields([
                            'position' => Input::make()->type('number')->min(1),
                            'round' => Select::make()->options([
                                'quarterfinal' => '1/4 финала',
                                'semifinal' => '1/2 финала',
                                'final' => 'Финал',
                            ]),
                            'description' => Input::make()->placeholder('Например: Победитель группы А'),
                        ])
                        ->value($this->config['special_byes'] ?? [])
                        ->help('Команды с указанных позиций сразу проходят в указанный раунд'),

                    CheckBox::make('config.reverse_seeding')
                        ->title('Обратный посев')
                        ->placeholder('Поменять местами верхнюю и нижнюю половину')
                        ->value($this->config['reverse_seeding'] ?? false)
                        ->help('Использовать обратный порядок при распределении'),
                ]),

                'Структура сетки' => Layout::rows([
                    Matrix::make('config.rounds_config')
                        ->title('Настройка раундов')
                        ->columns([
                            'Раунд' => 'name',
                            'Команд' => 'teams',
                            'Матчей' => 'matches',
                            'До побед' => 'best_of',
                            'Тай-брейк' => 'tie_breaker',
                        ])
                        ->fields([
                            'name' => Input::make()->placeholder('1/4 финала'),
                            'teams' => Input::make()->type('number')->min(2),
                            'matches' => Input::make()->type('number')->min(1),
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
                        ->help('Настройте каждый раунд плейофф'),
                ]),

                'Предпросмотр' => Layout::view('orchid.playoff-preview', [
                    'config' => $this->config,
                    'teams' => $this->getPreviewTeams(),
                ]),

                'Отладка' => $debugLayout,
            ]),
        ];
    }

    /**
     * Получает опции групп для select
     */
    private function getGroupsOptions(): array
    {
        $options = ['' => 'Все группы'];

        foreach ($this->stage->groups as $group) {
            $options[$group->id] = $group->name . ' (' . $group->teams->count() . ' команд)';
        }

        return $options;
    }

    /**
     * Получает тестовые команды для предпросмотра
     */
    private function getPreviewTeams(): array
    {
        $teams = [];
        $count = $this->config['total_teams'] ?? 8;

        for ($i = 1; $i <= $count; $i++) {
            $teams[] = [
                'position' => $i,
                'name' => "Команда {$i}",
            ];
        }

        return $teams;
    }

    /**
     * Сохранение настроек
     */
    public function save(Request $request)
    {
        $data = $request->input('config', []);

        // Валидация
        $request->validate([
            'config.format_type' => 'required|string',
            'config.total_teams' => 'required|integer|min:2',
        ]);

        // Логируем входящие данные для отладки
        \Log::info('Playoff config save data', $data);

        // Преобразуем reverse_seeding
        $reverseSeeding = isset($data['reverse_seeding']) && $data['reverse_seeding'] === 'on';

        // Обрабатываем special_byes
        $specialByes = $this->processSpecialByes($data['special_byes'] ?? []);

        // Обрабатываем rounds_config
        $roundsConfig = $this->processRoundsConfig($data['rounds_config'] ?? []);

        // Убеждаемся, что seeding_type сохраняется
        $seedingType = $data['seeding_type'] ?? 'standard';

        $configData = [
            'format_type' => $data['format_type'] ?? 'single_elimination',
            'total_teams' => (int)($data['total_teams'] ?? 8),
            'source_group_id' => $data['source_group_id'] ?? null,
            'description' => $data['description'] ?? null,
            'seeding_type' => $seedingType,
            'reverse_seeding' => $reverseSeeding,
            'special_byes' => $specialByes,
            'rounds_config' => $roundsConfig,
            'bracket_structure' => $this->generateBracketStructure($data),
            'matchups' => $this->generateMatchups($data, $seedingType, $reverseSeeding, $specialByes),
        ];

        try {
            $playoffConfig = $this->stage->playoffConfig;

            if ($playoffConfig) {
                $playoffConfig->update($configData);
                Toast::success('Настройки плейофф обновлены!');
            } else {
                $this->stage->playoffConfig()->create($configData);
                Toast::success('Настройки плейофф созданы!');
            }

            return redirect()->route('platform.tournament.groups', [
                'tournament' => $this->tournament->id,
                'stage' => $this->stage->id,
            ]);

        } catch (\Exception $e) {
            Toast::error('Ошибка при сохранении: ' . $e->getMessage());
            \Log::error('Playoff config save error: ' . $e->getMessage());
        }
    }


    /**
     * Получает данные конфигурации для формы
     */
    private function getConfigForDisplay()
    {
        $config = $this->stage->playoffConfig;

        if (!$config) {
            // Возвращаем структуру с пустыми значениями для формы
            return [
                'format_type' => 'single_elimination',
                'total_teams' => 8,
                'source_group_id' => null,
                'description' => null,
                'seeding_type' => 'standard',
                'reverse_seeding' => false,
                'special_byes' => [],
                'rounds_config' => [],
            ];
        }

        $configArray = $config->toArray();

        // Убеждаемся, что все необходимые поля есть
        $configArray['seeding_type'] = $configArray['seeding_type'] ?? 'standard';
        $configArray['reverse_seeding'] = $configArray['reverse_seeding'] ?? false;

        // Преобразуем reverse_seeding в формат для CheckBox (on/null)
        if ($configArray['reverse_seeding']) {
            $configArray['reverse_seeding'] = 'on';
        } else {
            $configArray['reverse_seeding'] = null;
        }

        // Преобразуем rounds_config для Matrix поля
        if (isset($configArray['rounds_config']) && is_array($configArray['rounds_config'])) {
            $roundsForMatrix = [];
            foreach ($configArray['rounds_config'] as $round) {
                $roundsForMatrix[] = [
                    'name' => $round['name'] ?? '',
                    'teams' => $round['teams'] ?? '',
                    'matches' => $round['matches'] ?? '',
                    'best_of' => (string)($round['best_of'] ?? '1'),
                    'tie_breaker' => $round['tie_breaker'] ?? 'golden_set',
                ];
            }
            $configArray['rounds_config'] = $roundsForMatrix;
        } else {
            $configArray['rounds_config'] = [];
        }

        // Преобразуем special_byes для Matrix поля
        if (isset($configArray['special_byes']) && is_array($configArray['special_byes'])) {
            $byesForMatrix = [];
            foreach ($configArray['special_byes'] as $bye) {
                $byesForMatrix[] = [
                    'position' => (string)($bye['position'] ?? ''),
                    'round' => $bye['round'] ?? '',
                    'description' => $bye['description'] ?? '',
                ];
            }
            $configArray['special_byes'] = $byesForMatrix;
        } else {
            $configArray['special_byes'] = [];
        }

        return $configArray;
    }

    /**
     * Обрабатывает специальные выходы
     */
    private function processSpecialByes(array $byes): array
    {
        $result = [];

        // Проверяем, является ли $byes массивом массивов
        if (isset($byes[0]) && is_array($byes[0])) {
            foreach ($byes as $bye) {
                if (!empty($bye['position']) && !empty($bye['round'])) {
                    $result[] = [
                        'position' => (int)$bye['position'],
                        'round' => $bye['round'],
                        'description' => $bye['description'] ?? '',
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Обрабатывает конфигурацию раундов
     */
    private function processRoundsConfig(array $rounds): array
    {
        $result = [];

        // Проверяем формат данных
        if (isset($rounds[0]) && is_array($rounds[0])) {
            // Данные приходят как индексированный массив
            foreach ($rounds as $index => $round) {
                if (!empty($round['name']) || !empty($round['teams'])) {
                    $result[$index + 1] = [
                        'name' => $round['name'] ?? 'Раунд ' . ($index + 1),
                        'teams' => (int)($round['teams'] ?? 0),
                        'matches' => (int)($round['matches'] ?? 0),
                        'best_of' => (int)($round['best_of'] ?? 1),
                        'tie_breaker' => $round['tie_breaker'] ?? 'golden_set',
                    ];
                }
            }
        } elseif (isset($rounds['name']) || isset($rounds['teams'])) {
            // Данные приходят как ассоциативный массив (один раунд)
            $result[1] = [
                'name' => $rounds['name'] ?? 'Раунд 1',
                'teams' => (int)($rounds['teams'] ?? 0),
                'matches' => (int)($rounds['matches'] ?? 0),
                'best_of' => (int)($rounds['best_of'] ?? 1),
                'tie_breaker' => $rounds['tie_breaker'] ?? 'golden_set',
            ];
        }

        // Если ничего не получили, генерируем автоматически
        if (empty($result)) {
            $totalTeams = $this->getTotalTeams();
            $result = $this->generateDefaultRoundsConfig($totalTeams);
        }

        return $result;
    }

    /**
     * Генерирует конфигурацию раундов по умолчанию
     */
    private function generateDefaultRoundsConfig(int $totalTeams): array
    {
        $result = [];
        $rounds = ceil(log($totalTeams, 2));
        $currentTeams = $totalTeams;

        for ($i = 1; $i <= $rounds; $i++) {
            $result[$i] = [
                'name' => $this->getRoundName($currentTeams, $i, $rounds),
                'teams' => $currentTeams,
                'matches' => $currentTeams / 2,
                'best_of' => 1,
                'tie_breaker' => 'golden_set',
            ];
            $currentTeams = $currentTeams / 2;
        }

        return $result;
    }

    /**
     * Получает общее количество команд
     */
    private function getTotalTeams(): int
    {
        return $this->stage->groups->sum(function ($group) {
            return $group->teams->count();
        }) ?: 8;
    }

    /**
     * Генерирует структуру сетки
     */
    private function generateBracketStructure(array $data): array
    {
        $totalTeams = (int)($data['total_teams'] ?? 8);
        $rounds = ceil(log($totalTeams, 2));
        $structure = [];

        for ($i = 1; $i <= $rounds; $i++) {
            $teamsInRound = $totalTeams / pow(2, $i - 1);
            $matchesInRound = $teamsInRound / 2;

            $structure[] = [
                'number' => $i,
                'name' => $this->getRoundName($teamsInRound, $i, $rounds),
                'teams' => $teamsInRound,
                'matches' => $matchesInRound,
            ];
        }

        return $structure;
    }

    /**
     * Генерирует распределение матчей (matchups) с учетом правил посева
     */
    private function generateMatchups(array $data, string $seedingType, bool $reverseSeeding, array $specialByes): array
    {
        $totalTeams = (int)($data['total_teams'] ?? 8);

        $matchups = [];
        $rounds = ceil(log($totalTeams, 2));

        // Генерируем первый раунд
        $firstRoundMatches = [];

        if ($seedingType === 'standard') {
            // Стандартный посев: 1-последний, 2-предпоследний...
            for ($i = 0; $i < $totalTeams / 2; $i++) {
                $homePos = $i + 1;
                $awayPos = $totalTeams - $i;

                // Проверяем специальные выходы
                $homeBye = $this->findByeForPosition($homePos, $specialByes);
                $awayBye = $this->findByeForPosition($awayPos, $specialByes);

                $firstRoundMatches[] = [
                    'home_position' => $homePos,
                    'away_position' => $awayPos,
                    'home_bye' => $homeBye,
                    'away_bye' => $awayBye,
                ];
            }
        } elseif ($seedingType === 'group_winners') {
            // Посев по победителям групп
            // TODO: Реализовать логику
            for ($i = 0; $i < $totalTeams / 2; $i++) {
                $firstRoundMatches[] = [
                    'home_position' => $i + 1,
                    'away_position' => $totalTeams - $i,
                ];
            }
        } else {
            // Случайный или ручной посев
            for ($i = 0; $i < $totalTeams / 2; $i++) {
                $firstRoundMatches[] = [
                    'home_position' => $i * 2 + 1,
                    'away_position' => $i * 2 + 2,
                ];
            }
        }

        if ($reverseSeeding) {
            $firstRoundMatches = array_reverse($firstRoundMatches);
        }

        $matchups[1] = $firstRoundMatches;

        // Генерируем последующие раунды
        $currentRound = 1;
        $matchesCount = count($firstRoundMatches);

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

    /**
     * Находит специальный выход для позиции
     */
    private function findByeForPosition(int $position, array $byes): ?array
    {
        foreach ($byes as $bye) {
            if ($bye['position'] == $position) {
                return $bye;
            }
        }
        return null;
    }

    /**
     * Определяет куда идет победитель
     */
    private function getWinnerDestination(int $matchIndex, int $totalRounds, array $byes): array
    {
        // TODO: Реализовать логику с учетом специальных выходов
        return [
            'round' => 2,
            'match' => ceil($matchIndex / 2),
            'position' => ($matchIndex % 2 === 1) ? 'home' : 'away',
        ];
    }

    /**
     * Получает название раунда
     */
    private function getRoundName(int $teams, int $roundNumber, int $totalRounds): string
    {
        if ($roundNumber === $totalRounds) {
            return 'Финал';
        }

        if ($roundNumber === $totalRounds - 1) {
            return '1/2 финала';
        }

        if ($roundNumber === $totalRounds - 2) {
            return '1/4 финала';
        }

        return "1/{$teams} финала";
    }

}

<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Game;
use App\Models\GameSet;
use App\Models\StageGroup;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use App\Models\User;
use App\Models\Venue;
use App\Orchid\Layouts\GameFilters;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GamesListScreen extends Screen
{
    public $tournament;
    public $stage;
    public $group;
    public $teams;

    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(Tournament $tournament, TournamentStage $stage, StageGroup $group): iterable
    {
        $this->tournament = $tournament;
        $this->stage = $stage;
        $this->group = $group;

        // Загружаем игры с минимальными отношениями
        $games = Game::where('group_id', $group->id)
            ->with([
                'homeApplication.team',
                'awayApplication.team',
                'venue',
                'firstReferee',
                'secondReferee',
                'sets'
            ])
            //->defaultSort('scheduled_time')
            ->orderBy('scheduled_time')
            //->get()
            ->paginate();

        // Загружаем команды только из текущей группы через промежуточную таблицу group_teams
        $teams = TournamentApplication::whereHas('groups', function ($query) use ($group) {
            $query->where('stage_groups.id', $group->id);
        })
            ->with('team')
            ->get();

        // Сохраняем teams для использования в getTeamsOptions
        $this->teams = $teams;

        return [
            'games' => $games,
            'teams' => $teams,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return "Игры: {$this->tournament->name} - {$this->stage->name} - {$this->group->name}";
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Добавить игру')
                ->icon('plus')
                ->modal('createGameModal')
                ->method('createGame'),

            Button::make('Сгенерировать все игры')
                ->icon('magic')
                ->method('generateAllGames')
                ->confirm('Сгенерировать все игры между командами этой группы? Каждая команда сыграет с каждой по одному разу.')
                ->canSee(!$this->group->games()->exists()),

            Link::make('Назад к выбору')
                ->icon('arrow-left')
                ->route('platform.tournament.games.management'),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            //GameFilters::class,
            // Модальное окно создания игры
            Layout::modal('createGameModal', [
                Layout::rows([
                    // Команды в одну строку
                    Group::make([
                        Select::make('game.home_application_id')
                            ->title('Хозяева')
                            ->required()
                            ->options($this->getTeamsOptions())
                            ->help('Команда хозяев'),

                        Select::make('game.away_application_id')
                            ->title('Гости')
                            ->required()
                            ->options($this->getTeamsOptions())
                            ->help('Команда гостей'),
                    ]),

                    // Дата и время в одну строку
                    Group::make([
                        DateTimer::make('game.scheduled_date')
                            ->title('Дата игры')
                            ->required()
                            ->format('d.m.Y')
                            ->allowInput()
                            ->placeholder('Выберите дату'),

                        DateTimer::make('game.scheduled_time')
                            ->title('Время игры')
                            ->required()
                            ->enableTime()
                            ->noCalendar()
                            ->format('H:i')
                            ->format24hr()
                            ->allowInput()
                            ->placeholder('Выберите время'),
                    ]),

                    // Зал
                    Relation::make('game.venue_id')
                        ->title('Зал проведения')
                        ->required()
                        ->fromModel(Venue::class, 'name')
                        ->displayAppend('display_name')
                        ->help('По умолчанию - домашний зал команды хозяев'),

                    // Судьи в одну строку
                    Group::make([
                        Select::make('game.first_referee_id')
                            ->title('Первый судья')
                            ->empty('Не выбран')
                            ->fromModel(User::class, 'name'),

                        Select::make('game.second_referee_id')
                            ->title('Второй судья')
                            ->empty('Не выбран')
                            ->fromModel(User::class, 'name'),
                    ]),
                ])
            ])
                ->title('Создать игру')
                ->applyButton('Создать')
                ->closeButton('Отмена'),

            // Модальное окно редактирования игры
            Layout::modal('editGameModal', [
                Layout::rows([
                    Input::make('game.id')
                        ->type('hidden'),

                    // Команды в одну строку
                    Group::make([
                        Select::make('game.home_application_id')
                            ->title('Хозяева')
                            ->required()
                            ->options($this->getTeamsOptions()),

                        Select::make('game.away_application_id')
                            ->title('Гости')
                            ->required()
                            ->options($this->getTeamsOptions()),
                    ]),

                    // Дата и время в одну строку
                    Group::make([
                        DateTimer::make('game.scheduled_date')
                            ->title('Дата игры')
                            ->required()
                            ->format('d.m.Y')
                            ->allowInput()
                            ->placeholder('Выберите дату'),

                        DateTimer::make('game.scheduled_time')
                            ->title('Время игры')
                            ->required()
                            ->enableTime()
                            ->noCalendar()
                            ->format('H:i')
                            ->format24hr()
                            ->allowInput()
                            ->placeholder('Выберите время'),
                    ]),

                    // Зал
                    Relation::make('game.venue_id')
                        ->title('Зал проведения')
                        ->required()
                        ->fromModel(Venue::class, 'name')
                        ->displayAppend('display_name'),

                    // Судьи в одну строку
                    Group::make([
                        Select::make('game.first_referee_id')
                            ->title('Первый судья')
                            ->empty('Не выбран')
                            ->fromModel(User::class, 'name'),

                        Select::make('game.second_referee_id')
                            ->title('Второй судья')
                            ->empty('Не выбран')
                            ->fromModel(User::class, 'name'),
                    ]),
                ])
            ])
                ->title('Редактировать игру')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->async('asyncGetGame'),

            // Модальное окно для ввода результатов
            Layout::modal('scoreGameModal', [
                Layout::rows([
                    Input::make('game.id')
                        ->type('hidden'),

                    // Общий счет
                    Group::make([
                        Input::make('game.home_score')
                            ->title('Счет хозяев')
                            ->type('number')
                            ->min(0)
                            ->required()
                            ->help('Общий счет команды хозяев'),

                        Input::make('game.away_score')
                            ->title('Счет гостей')
                            ->type('number')
                            ->min(0)
                            ->required()
                            ->help('Общий счет команды гостей'),
                    ]),

                    // Сеты
                    Matrix::make('game.sets')
                        ->title('Результаты по сетам')
                        ->columns([
//                            'set_number' => 'Сет',
//                            'home_score' => 'Хозяева',
//                            'away_score' => 'Гости',

                            'Сет' => 'set_number',
                            'Хозяева' => 'home_score',
                            'Гости' => 'away_score',
                        ])
                        ->fields([
                            'set_number' => Input::make('set_number')
                                ->type('number')
                                ->min(1)
                                ->max(5)
                                ->readonly(),
                            'home_score' => Input::make('home_score')
                                ->type('number')
                                ->min(0),
                            'away_score' => Input::make('away_score')
                                ->type('number')
                                ->min(0),
                        ])
                        ->maxRows(5)
                        ->help('Заполните результаты по сетам (максимум 5 сетов)'),
                ])
            ])
                ->title('Внести результат игры')
                ->applyButton('Сохранить результат')
                ->closeButton('Отмена')
                ->async('asyncGetGameForScore'),

            Layout::table('games', [
                TD::make('teams', 'Команды')
                    ->render(function (Game $game) {
                        $homeTeam = $game->homeApplication->team->name ?? 'Неизвестно';
                        $awayTeam = $game->awayApplication->team->name ?? 'Неизвестно';

                        $score = '';
                        if ($game->home_score !== null && $game->away_score !== null) {
                            $score = "<br><small class='text-success'><b>{$game->home_score} - {$game->away_score}</b></small>";

                            // Показываем сеты если они есть
                            if ($game->sets->count() > 0) {
                                $sets = $game->sets->map(function($set) {
                                    return "{$set->home_score}:{$set->away_score}";
                                })->implode(', ');
                                $score .= "<br><small class='text-muted'>Сеты: {$sets}</small>";
                            }
                        }

                        return "<b>{$homeTeam}</b> vs <b>{$awayTeam}</b>{$score}";
                    }),

                TD::make('date', 'Дата игры')
                    ->render(function (Game $game) {
                        return $game->scheduled_time->format('d.m.Y');
                    }),

                TD::make('time', 'Время игры')
                    ->render(function (Game $game) {
                        return $game->scheduled_time->format('H:i');
                    }),

                TD::make('venue', 'Зал')
                    ->render(function (Game $game) {
                        return $game->venue->name . ', ' . $game->venue->address ?? 'Не указан';
                    }),

                TD::make('actions', 'Действия')
                    ->alignRight()
                    ->render(function (Game $game) {
                        return DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([
                                ModalToggle::make('Редактировать')
                                    ->icon('pencil')
                                    ->modal('editGameModal')
                                    ->method('updateGame')
                                    ->asyncParameters(['game' => $game->id]),

                                ModalToggle::make('Внести результат')
                                    ->icon('clipboard-check')
                                    ->modal('scoreGameModal')
                                    ->method('updateGameScore')
                                    ->asyncParameters(['game' => $game->id]),
                                    //->canSee(!$game->isCompleted()),

                                Button::make('Удалить')
                                    ->icon('trash')
                                    ->method('deleteGame', ['game_id' => $game->id])
                                    ->confirm('Вы уверены, что хотите удалить эту игру?'),
                            ]);
                    }),
            ])->title('Список игр'),
        ];
    }

    /**
     * Получает опции команд для select
     */
    private function getTeamsOptions(): array
    {
        return $this->teams->mapWithKeys(function ($application) {
            return [$application->id => $application->team->name];
        })->toArray();
    }

    /**
     * Async метод для получения данных игры для редактирования
     */
    public function asyncGetGame(int $game): array
    {
        $game = Game::findOrFail($game);

        return [
            'game' => [
                'id' => $game->id,
                'home_application_id' => $game->home_application_id,
                'away_application_id' => $game->away_application_id,
                'venue_id' => $game->venue_id,
                'scheduled_date' => $game->scheduled_time->format('d.m.Y'),
                'scheduled_time' => $game->scheduled_time->format('H:i'),
                'first_referee_id' => $game->first_referee_id,
                'second_referee_id' => $game->second_referee_id,
            ],
        ];
    }

    /**
     * Async метод для получения данных игры для ввода результатов
     */
    /**
     * Async метод для получения данных игры для ввода результатов
     */
    public function asyncGetGameForScore(int $game): array
    {
        $game = Game::with(['sets', 'homeApplication.team', 'awayApplication.team'])->findOrFail($game);

        // Получаем названия команд
        $homeTeamName = $game->homeApplication->team->name ?? 'Хозяева';
        $awayTeamName = $game->awayApplication->team->name ?? 'Гости';

        // Подготавливаем сеты
        $sets = [];
        for ($i = 1; $i <= 5; $i++) {
            $set = $game->sets->firstWhere('set_number', $i);
            $sets[] = [
                'set_number' => $i,
                'home_score' => $set->home_score ?? 0,
                'away_score' => $set->away_score ?? 0,
            ];
        }

        return [
            'game' => [
                'id' => $game->id,
                'home_score' => $game->home_score ?? 0,
                'away_score' => $game->away_score ?? 0,
            ],
            'game.sets' => $sets,
            'home_team_name' => $homeTeamName, // Добавляем названия команд
            'away_team_name' => $awayTeamName,
        ];
    }

    /**
     * Создание игры
     */
    public function createGame(Request $request)
    {
        try {
            $request->validate([
                'game.home_application_id' => 'required|exists:tournament_applications,id',
                'game.away_application_id' => 'required|exists:tournament_applications,id|different:game.home_application_id',
                'game.scheduled_date' => 'required|date',
                'game.scheduled_time' => 'required',
                'game.venue_id' => 'required|exists:venues,id',
            ]);

            // Объединяем дату и время (преобразуем из d.m.Y в Y-m-d)
            $scheduledDate = \Carbon\Carbon::createFromFormat('d.m.Y', $request->input('game.scheduled_date'))->format('Y-m-d');
            $scheduledDateTime = $scheduledDate . ' ' . $request->input('game.scheduled_time');

            Game::create([
                'stage_id' => $this->stage->id,
                'group_id' => $this->group->id,
                'home_application_id' => $request->input('game.home_application_id'),
                'away_application_id' => $request->input('game.away_application_id'),
                'venue_id' => $request->input('game.venue_id'),
                'scheduled_time' => $scheduledDateTime,
                'first_referee_id' => $request->input('game.first_referee_id'),
                'second_referee_id' => $request->input('game.second_referee_id'),
                'status' => 'scheduled',
            ]);

            Toast::success('Игра успешно создана!');

        } catch (\Exception $e) {
            Toast::error('Ошибка при создании игры: ' . $e->getMessage());
        }
    }

    /**
     * Обновление игры
     */
    public function updateGame(Request $request)
    {
        try {
            $request->validate([
                'game.id' => 'required|exists:games,id',
                'game.home_application_id' => 'required|exists:tournament_applications,id',
                'game.away_application_id' => 'required|exists:tournament_applications,id|different:game.home_application_id',
                'game.scheduled_date' => 'required|date',
                'game.scheduled_time' => 'required',
                'game.venue_id' => 'required|exists:venues,id',
            ]);

            // Объединяем дату и время (преобразуем из d.m.Y в Y-m-d)
            $scheduledDate = \Carbon\Carbon::createFromFormat('d.m.Y', $request->input('game.scheduled_date'))->format('Y-m-d');
            $scheduledDateTime = $scheduledDate . ' ' . $request->input('game.scheduled_time');

            $game = Game::findOrFail($request->input('game.id'));
            $game->update([
                'home_application_id' => $request->input('game.home_application_id'),
                'away_application_id' => $request->input('game.away_application_id'),
                'venue_id' => $request->input('game.venue_id'),
                'scheduled_time' => $scheduledDateTime,
                'first_referee_id' => $request->input('game.first_referee_id'),
                'second_referee_id' => $request->input('game.second_referee_id'),
            ]);

            Toast::success('Игра успешно обновлена!');

        } catch (\Exception $e) {
            Toast::error('Ошибка при обновлении игры: ' . $e->getMessage());
        }
    }

    /**
     * Обновление результатов игры
     */
    public function updateGameScore(Request $request)
    {
        try {
            $request->validate([
                'game.id' => 'required|exists:games,id',
                'game.home_score' => 'required|integer|min:0',
                'game.away_score' => 'required|integer|min:0',
            ]);

            $game = Game::findOrFail($request->input('game.id'));

            // Обновляем общий счет и статус
            $game->update([
                'home_score' => $request->input('game.home_score'),
                'away_score' => $request->input('game.away_score'),
                'status' => 'completed',
            ]);

            // Удаляем все существующие сеты этой игры
            GameSet::where('game_id', $game->id)->delete();

            // Добавляем новые сеты (только с ненулевым счетом)
            $sets = $request->input('game.sets', []);
            foreach ($sets as $setData) {
                if (($setData['home_score'] ?? 0) != 0 || ($setData['away_score'] ?? 0) != 0) {
                    GameSet::create([
                        'game_id' => $game->id,
                        'set_number' => $setData['set_number'],
                        'home_score' => $setData['home_score'] ?? 0,
                        'away_score' => $setData['away_score'] ?? 0,
                    ]);
                }
            }

            Toast::success('Результат игры успешно сохранен!');

        } catch (\Exception $e) {
            Toast::error('Ошибка при сохранении результата: ' . $e->getMessage());
        }
    }

    /**
     * Удаление игры
     */
    public function deleteGame(Request $request)
    {
        try {
            $game = Game::findOrFail($request->input('game_id'));
            $game->delete();

            Toast::info('Игра удалена');

        } catch (\Exception $e) {
            Toast::error('Ошибка при удалении игры: ' . $e->getMessage());
        }
    }

    /**
     * Генерация всех игр между командами группы
     */
    public function generateAllGames()
    {
        try {
            // Проверяем, что в группе еще нет игр
            if ($this->group->games()->exists()) {
                Toast::error('Невозможно сгенерировать игры: в группе уже есть созданные игры');
                return;
            }

            $teams = $this->teams;

            if ($teams->count() < 2) {
                Toast::error('Для генерации игр нужно минимум 2 команды в группе');
                return;
            }

            $baseDate = now()->startOfDay()->addHours(10); // Начинаем с 10:00
            $gameInterval = 2; // Интервал между играми в часах
            $gameCount = 0;

            // Создаем все возможные пары команд (каждая с каждой)
            for ($i = 0; $i < $teams->count(); $i++) {
                for ($j = $i + 1; $j < $teams->count(); $j++) {
                    $homeApplication = $teams[$i];
                    $awayApplication = $teams[$j];

                    // Получаем домашний зал команды хозяев
                    $venueId = $homeApplication->team->venue->id ?? Venue::first()->id;

                    // Рассчитываем время игры
                    $scheduledTime = $baseDate->copy()->addHours($gameCount * $gameInterval);

                    Game::create([
                        'stage_id' => $this->stage->id,
                        'group_id' => $this->group->id,
                        'home_application_id' => $homeApplication->id,
                        'away_application_id' => $awayApplication->id,
                        'venue_id' => $venueId,
                        'scheduled_time' => $scheduledTime,
                        'status' => 'scheduled',
                    ]);

                    $gameCount++;
                }
            }

            Toast::success("Сгенерировано {$gameCount} игр для группы {$this->group->name}");

        } catch (\Exception $e) {
            Toast::error('Ошибка при генерации игр: ' . $e->getMessage());
        }
    }
}

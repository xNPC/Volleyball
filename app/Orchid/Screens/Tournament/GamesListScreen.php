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
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GamesListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(Tournament $tournament, TournamentStage $stage, StageGroup $group): iterable
    {
        // Загружаем игры с отношениями
        $games = Game::where('group_id', $group->id)
            ->with([
                'homeApplication.team',
                'awayApplication.team',
                'venue',
                'sets',
                'firstReferee',
                'secondReferee'
            ])
            ->get();

        // Загружаем команды группы
        $teams = $group->teams()->with('team')->get();

        return [
            'tournament' => $tournament,
            'stage' => $stage,
            'group' => $group,
            'games' => $games,
            'teams' => $teams,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
//        $tournament = $this->tournament;
//        $stage = $this->stage;
//        $group = $this->group;
//
//        if ($tournament && $stage && $group) {
//            return "Игры: {$tournament->name} - {$stage->name} - {$group->name}";
//        }

        return 'Управление играми';
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
                ->confirm('Сгенерировать все игры между командами этой группы?'),
                //->canSee(!$this->group->games()->exists()),

            Button::make('Назад к выбору')
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
            // Модальное окно создания игры
            Layout::modal('createGameModal', [
                Layout::rows([
                    Select::make('game.home_application_id')
                        ->title('Хозяева')
                        ->required()
                        ->options($this->getTeamsOptions()),

                    Select::make('game.away_application_id')
                        ->title('Гости')
                        ->required()
                        ->options($this->getTeamsOptions()),

                    DateTimer::make('game.scheduled_time')
                        ->title('Дата и время')
                        ->required()
                        ->enableTime()
                        ->format('Y-m-d H:i:s'),

                    Select::make('game.first_referee_id')
                        ->title('Первый судья')
                        ->empty('Не выбран')
                        ->fromModel(User::class, 'name'),

                    Select::make('game.second_referee_id')
                        ->title('Второй судья')
                        ->empty('Не выбран')
                        ->fromModel(User::class, 'name'),
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

                    Select::make('game.home_application_id')
                        ->title('Хозяева')
                        ->required()
                        ->options($this->getTeamsOptions()),

                    Select::make('game.away_application_id')
                        ->title('Гости')
                        ->required()
                        ->options($this->getTeamsOptions()),

                    Select::make('game.venue_id')
                        ->title('Место проведения')
                        ->required()
                        ->fromModel(Venue::class, 'name'),

                    DateTimer::make('game.scheduled_time')
                        ->title('Дата и время')
                        ->required()
                        ->enableTime()
                        ->format('Y-m-d H:i:s'),

                    Select::make('game.status')
                        ->title('Статус')
                        ->required()
                        ->options([
                            'scheduled' => 'Запланирована',
                            'live' => 'В прямом эфире',
                            'completed' => 'Завершена',
                            'cancelled' => 'Отменена',
                        ]),

                    Select::make('game.first_referee_id')
                        ->title('Первый судья')
                        ->empty('Не выбран')
                        ->fromModel(User::class, 'name'),

                    Select::make('game.second_referee_id')
                        ->title('Второй судья')
                        ->empty('Не выбран')
                        ->fromModel(User::class, 'name'),
                ])
            ])
                ->title('Редактировать игру')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->async('asyncGetGame'),

            // Модальное окно для ввода результата
            Layout::modal('scoreGameModal', [
                Layout::rows([
                    Input::make('game.id')
                        ->type('hidden'),

                    Group::make([
                        Input::make('game.home_score')
                            ->title('Счет хозяев')
                            ->type('number')
                            ->min(0)
                            ->value(0),

                        Input::make('game.away_score')
                            ->title('Счет гостей')
                            ->type('number')
                            ->min(0)
                            ->value(0),
                    ]),

                    Matrix::make('game.sets')
                        ->title('Сеты')
                        ->columns([
                            'set_number' => 'Сет',
                            'home_score' => 'Хозяева',
                            'away_score' => 'Гости',
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
                        ->maxRows(5),
                ])
            ])
                ->title('Внести результат')
                ->applyButton('Сохранить')
                ->closeButton('Отмена')
                ->async('asyncGetGame'),

            // Таблица игр
            Layout::table('games', [
//                TD::make('scheduled_time', 'Дата и время')
//                    ->sort()
//                    ->render(function (Game $game) {
//                        return $game->scheduled_time->format('d.m.Y H:i');
//                    }),

                TD::make('teams', 'Команды')
                    ->render(function (Game $game) {
                        $homeTeam = $game->homeApplication->team->name ?? 'Неизвестно';
                        $awayTeam = $game->awayApplication->team->name ?? 'Неизвестно';
                        return "{$homeTeam} vs {$awayTeam}";
                    }),

                TD::make('venue', 'Место')
                    ->render(function (Game $game) {
                        return $game->venue->name ?? '-';
                    }),

                TD::make('score', 'Счет')
                    ->render(function (Game $game) {
                        if ($game->status === 'completed') {
                            $score = "{$game->home_score} - {$game->away_score}";
                            if ($game->sets->count() > 0) {
                                $sets = $game->sets->map(function($set) {
                                    return "{$set->home_score}:{$set->away_score}";
                                })->implode(', ');
                                return "{$score}<br><small>Сеты: {$sets}</small>";
                            }
                            return $score;
                        }
                        return '<span class="text-muted">-</span>';
                    }),

                TD::make('status', 'Статус')
                    ->render(function (Game $game) {
                        $statuses = [
                            'scheduled' => ['label' => 'Запланирована', 'color' => Color::SECONDARY],
                            'live' => ['label' => 'В прямом эфире', 'color' => Color::WARNING],
                            'completed' => ['label' => 'Завершена', 'color' => Color::SUCCESS],
                            'cancelled' => ['label' => 'Отменена', 'color' => Color::DANGER],
                        ];

                        $status = $statuses[$game->status] ?? $statuses['scheduled'];

                        return \Orchid\Screen\Fields\Label::make()
                            ->value($status['label'])
                            ->type($status['color']);
                    }),

                TD::make('actions', 'Действия')
                    ->alignRight()
                    ->render(function (Game $game) {
                        return Group::make([
                            ModalToggle::make('Редактировать')
                                ->icon('pencil')
                                ->modal('editGameModal')
                                ->method('updateGame')
                                ->asyncParameters(['game' => $game->id]),

                            ModalToggle::make('Результат')
                                ->icon('clipboard-check')
                                ->modal('scoreGameModal')
                                ->method('updateGameScore')
                                ->asyncParameters(['game' => $game->id])
                                ->canSee($game->status !== 'completed'),

                            Button::make('Удалить')
                                ->icon('trash')
                                ->confirm('Удалить эту игру?')
                                ->method('deleteGame', ['game' => $game->id]),
                        ])->autoWidth();
                    }),
            ])->title('Список игр'),
        ];
    }

    /**
     * Получает опции команд для select
     */
    private function getTeamsOptions(): array
    {
        $teams = $this->teams ?? collect();

        return $teams->mapWithKeys(function ($application) {
            return [$application->id => $application->team->name];
        })->toArray();
    }

    /**
     * Async метод для получения данных игры
     */
    public function asyncGetGame(int $game): array
    {
        $game = Game::with(['sets'])->findOrFail($game);

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
                'home_application_id' => $game->home_application_id,
                'away_application_id' => $game->away_application_id,
                'venue_id' => $game->venue_id,
                'scheduled_time' => $game->scheduled_time->format('Y-m-d H:i:s'),
                'status' => $game->status,
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
                'first_referee_id' => $game->first_referee_id,
                'second_referee_id' => $game->second_referee_id,
            ],
            'game.sets' => $sets,
        ];
    }

    /**
     * Создание игры
     */
    public function createGame(Tournament $tournament, TournamentStage $stage, StageGroup $group, Request $request)
    {
        $request->validate([
            'game.home_application_id' => 'required|exists:tournament_applications,id',
            'game.away_application_id' => 'required|exists:tournament_applications,id|different:game.home_application_id',
            'game.scheduled_time' => 'required|date',
        ]);

        try {
            // Получаем домашний зал команды хозяев
            $homeApplication = TournamentApplication::with(['team.venue'])->find($request->input('game.home_application_id'));
            $venueId = $homeApplication->team->venue->id ?? Venue::first()->id;

            Game::create([
                'stage_id' => $stage->id,
                'group_id' => $group->id,
                'home_application_id' => $request->input('game.home_application_id'),
                'away_application_id' => $request->input('game.away_application_id'),
                'venue_id' => $venueId,
                'scheduled_time' => $request->input('game.scheduled_time'),
                'first_referee_id' => $request->input('game.first_referee_id'),
                'second_referee_id' => $request->input('game.second_referee_id'),
                'status' => 'scheduled',
            ]);

            Toast::success('Игра успешно создана');
        } catch (\Exception $e) {
            Toast::error('Ошибка при создании игры: ' . $e->getMessage());
        }
    }

    /**
     * Генерация всех игр между командами группы
     */
    public function generateAllGames(Tournament $tournament, TournamentStage $stage, StageGroup $group)
    {
        try {
            $teams = $group->teams;

            if ($teams->count() < 2) {
                Toast::error('Для генерации игр нужно минимум 2 команды в группе');
                return;
            }

            $gameCount = 0;
            $baseDate = now()->startOfDay()->addHours(10);

            // Создаем игры между всеми командами
            for ($i = 0; $i < $teams->count(); $i++) {
                for ($j = $i + 1; $j < $teams->count(); $j++) {
                    $homeTeam = $teams[$i];
                    $awayTeam = $teams[$j];

                    // Получаем домашний зал
                    $venueId = $homeTeam->team->venue->id ?? Venue::first()->id;

                    Game::create([
                        'stage_id' => $stage->id,
                        'group_id' => $group->id,
                        'home_application_id' => $homeTeam->id,
                        'away_application_id' => $awayTeam->id,
                        'venue_id' => $venueId,
                        'scheduled_time' => $baseDate->copy()->addHours($gameCount * 2),
                        'status' => 'scheduled',
                    ]);

                    $gameCount++;
                }
            }

            Toast::success("Сгенерировано {$gameCount} игр");
        } catch (\Exception $e) {
            Toast::error('Ошибка при генерации игр: ' . $e->getMessage());
        }
    }

    /**
     * Обновление игры
     */
    public function updateGame(Request $request)
    {
        $request->validate([
            'game.id' => 'required|exists:games,id',
            'game.home_application_id' => 'required|exists:tournament_applications,id',
            'game.away_application_id' => 'required|exists:tournament_applications,id|different:game.home_application_id',
            'game.venue_id' => 'required|exists:venues,id',
            'game.scheduled_time' => 'required|date',
            'game.status' => 'required|in:scheduled,live,completed,cancelled',
        ]);

        try {
            $game = Game::findOrFail($request->input('game.id'));
            $game->update($request->input('game'));

            Toast::success('Игра обновлена');
        } catch (\Exception $e) {
            Toast::error('Ошибка при обновлении игры: ' . $e->getMessage());
        }
    }

    /**
     * Обновление счета игры
     */
    public function updateGameScore(Request $request)
    {
        $request->validate([
            'game.id' => 'required|exists:games,id',
            'game.home_score' => 'required|integer|min:0',
            'game.away_score' => 'required|integer|min:0',
        ]);

        try {
            $game = Game::findOrFail($request->input('game.id'));

            // Обновляем основную информацию
            $game->update([
                'home_score' => $request->input('game.home_score'),
                'away_score' => $request->input('game.away_score'),
                'status' => 'completed',
            ]);

            // Обновляем сеты
            $sets = $request->input('game.sets', []);
            foreach ($sets as $setData) {
                if (!empty($setData['home_score']) || !empty($setData['away_score'])) {
                    GameSet::updateOrCreate(
                        [
                            'game_id' => $game->id,
                            'set_number' => $setData['set_number'],
                        ],
                        [
                            'home_score' => $setData['home_score'] ?? 0,
                            'away_score' => $setData['away_score'] ?? 0,
                        ]
                    );
                }
            }

            Toast::success('Результат сохранен');
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
            $game = Game::findOrFail($request->input('game'));
            $game->delete();

            Toast::info('Игра удалена');
        } catch (\Exception $e) {
            Toast::error('Ошибка при удалении игры: ' . $e->getMessage());
        }
    }
}

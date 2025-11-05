<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Game;
use App\Models\StageGroup;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class GamesListScreen extends Screen
{
//    public Tournament $tournament;
//    public TournamentStage $stage;
//    public StageGroup $group;

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
                'venue'
            ])
            ->orderBy('scheduled_time')
            ->get();

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

            // Модальное окно создания игры
//            Layout::modal('createGameModal', [
//                Layout::rows([
//                    Select::make('game.home_application_id')
//                        ->title('Хозяева')
//                        ->required()
//                        ->options($this->getTeamsOptions())
//                        ->help('Команда хозяев'),
//
//                    Select::make('game.away_application_id')
//                        ->title('Гости')
//                        ->required()
//                        ->options($this->getTeamsOptions())
//                        ->help('Команда гостей'),
//
//                    DateTimer::make('game.scheduled_time')
//                        ->title('Дата и время')
//                        ->required()
//                        ->enableTime()
//                        ->format('Y-m-d H:i'),
//
//                    Select::make('game.first_referee_id')
//                        ->title('Первый судья')
//                        ->empty('Не выбран')
//                        ->fromModel(User::class, 'name'),
//
//                    Select::make('game.second_referee_id')
//                        ->title('Второй судья')
//                        ->empty('Не выбран')
//                        ->fromModel(User::class, 'name'),
//                ])
//            ])
//                ->title('Создать игру')
//                ->applyButton('Создать')
//                ->closeButton('Отмена'),

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
                            ->format('Y-m-d')
                            ->value(now()->format('Y-m-d')),

                        DateTimer::make('game.scheduled_time')
                            ->title('Время игры')
                            ->required()
                            ->enableTime()
                            ->noCalendar()
                            ->format('H:i')
                            ->value('18:00'),
                    ]),

                    // Зал
                    Select::make('game.venue_id')
                        ->title('Зал проведения')
                        ->required()
                        ->fromModel(Venue::class, 'name')
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

            Layout::table('games', [
                TD::make('teams', 'Команды')
                    ->render(function (Game $game) {
                        $homeTeam = $game->homeApplication->team->name ?? 'Неизвестно';
                        $awayTeam = $game->awayApplication->team->name ?? 'Неизвестно';
                        return "{$homeTeam} vs {$awayTeam}";
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
                        return $game->venue->name ?? 'Не указан';
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

            // Объединяем дату и время
            $scheduledDateTime = $request->input('game.scheduled_date') . ' ' . $request->input('game.scheduled_time');

            // Если зал не выбран, используем домашний зал команды хозяев
            $venueId = $request->input('game.venue_id');
            if (!$venueId) {
                $homeApplication = TournamentApplication::with(['team.venue'])->find($request->input('game.home_application_id'));
                $venueId = $homeApplication->team->venue->id ?? Venue::first()->id;
            }

            Game::create([
                'stage_id' => $this->stage->id,
                'group_id' => $this->group->id,
                'home_application_id' => $request->input('game.home_application_id'),
                'away_application_id' => $request->input('game.away_application_id'),
                'venue_id' => $venueId,
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
}

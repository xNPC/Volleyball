<?php

namespace App\Orchid\Screens\RosterRequest;

use App\Models\ApplicationRoster;
use App\Models\RosterRequest;
use App\Models\TournamentApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\Response;

class RosterRequestFormScreen extends Screen
{
    public $application;

    public $applicationOptions = [];

    public function query(TournamentApplication $application = null): iterable
    {
        $this->application = $application ?? new TournamentApplication();

        $user = auth()->user();
        $isAdmin = $user->hasAccess('platform.applications.edit');

        $applicationsQuery = TournamentApplication::query()
            ->where('status', 'approved')
            ->whereHas('tournament', fn ($q) => $q->where('status', '!=', 'completed'))
            ->with(['team', 'tournament']);

        if (!$isAdmin) {
            $applicationsQuery->whereHas('team', function ($q) use ($user) {
                $q->where('captain_id', $user->id);
            });
        }

        $this->applicationOptions = $applicationsQuery->get()
            ->mapWithKeys(fn (TournamentApplication $a) => [
                $a->id => $a->team->name . ' — ' . $a->tournament->name,
            ])
            ->toArray();

        return [
            'applicationOptions' => $this->applicationOptions,
            'request' => [
                'type' => request()->get('type'),
                'player_user_id' => request()->get('player'),
                'application_id' => $this->application->exists ? $this->application->id : null,
            ],
        ];
    }

    public function name(): ?string
    {
        return 'Дозаявка / переход / отзаявка игрока';
    }

    public function description(): ?string
    {
        return 'Запрос будет рассмотрен администратором. Действуют дедлайны турнира и лимит переходов.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('К списку запросов')
                ->icon('bs.box-arrow-right')
                ->route('platform.roster.requests.list'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Select::make('request.type')
                    ->title('Тип запроса')
                    ->options(RosterRequest::TYPES)
                    ->required(),

                Select::make('request.application_id')
                    ->title('Заявка команды')
                    ->empty('Выберите заявку')
                    ->options($this->applicationOptions)
                    ->required()
                    ->help('Для дозаявки и перехода — команда, куда добавляем игрока. Для отзаявки — команда, из которой убираем.'),

                Relation::make('request.player_user_id')
                    ->fromModel(User::class, 'name')
                    ->title('Игрок')
                    ->required(),

                Group::make([
                    Input::make('request.jersey_number')
                        ->type('number')
                        ->title('Игровой номер')
                        ->min(1)
                        ->max(99),

                    Select::make('request.position')
                        ->options(ApplicationRoster::POSITIONS)
                        ->title('Амплуа')
                        ->empty('Не выбрано'),
                ])
                    ->autoWidth(),

                TextArea::make('request.comment')
                    ->title('Комментарий')
                    ->rows(3)
                    ->max(1000)
                    ->help('Необязательно. Пояснение для администратора.'),

                Group::make([
                    Button::make('Отправить запрос')
                        ->icon('bs.send')
                        ->type(Color::PRIMARY)
                        ->method('submit'),
                ])
                    ->autoWidth(),
            ]),
        ];
    }

    public function submit(Request $request): Response
    {
        $validated = $request->validate([
            'request.type' => 'required|in:addition,transfer,removal',
            'request.application_id' => 'required|exists:tournament_applications,id',
            'request.player_user_id' => 'required|exists:users,id',
            'request.jersey_number' => 'required_unless:request.type,removal|integer|min:1|max:99',
            'request.position' => 'required_unless:request.type,removal|string|max:50',
            'request.comment' => 'nullable|string|max:1000',
        ])['request'];

        $user = auth()->user();
        $application = TournamentApplication::with(['team', 'tournament'])->findOrFail($validated['application_id']);
        $player = User::findOrFail($validated['player_user_id']);
        $tournament = $application->tournament;

        if (!$user->hasAccess('platform.applications.edit') && $application->team->captain_id !== $user->id) {
            abort(403, 'Вы не являетесь капитаном этой команды');
        }

        if ($application->status !== 'approved') {
            Toast::error('Заявка команды должна быть утверждена');
            return back();
        }

        if (RosterRequest::where('tournament_id', $tournament->id)
            ->where('player_user_id', $player->id)
            ->where('status', RosterRequest::STATUS_PENDING)
            ->exists()) {
            Toast::error('По этому игроку уже есть запрос, ожидающий рассмотрения');
            return back();
        }

        $data = [
            'tournament_id' => $tournament->id,
            'player_user_id' => $player->id,
            'comment' => $validated['comment'] ?? null,
            'created_by' => $user->id,
        ];

        if ($validated['type'] === RosterRequest::TYPE_REMOVAL) {
            $hasRow = ApplicationRoster::where('application_id', $application->id)
                ->where('user_id', $player->id)
                ->exists();

            if (!$hasRow) {
                Toast::error('Игрок не найден в составе этой заявки');
                return back();
            }

            $data += [
                'type' => RosterRequest::TYPE_REMOVAL,
                'from_application_id' => $application->id,
                'to_application_id' => null,
            ];
        } else {
            if (!$player->gender) {
                Toast::error('У игрока не указан пол. Попросите администратора заполнить это в профиле.');
                return back();
            }

            $deadlineField = ($validated['type'] === RosterRequest::TYPE_TRANSFER
                    ? 'transfer_deadline_'
                    : 'addition_deadline_') . $player->gender;
            $deadline = $tournament->$deadlineField;

            if ($deadline !== null && now()->startOfDay()->gt(\Carbon\Carbon::parse($deadline))) {
                Toast::error('Срок подачи истёк (' . \Carbon\Carbon::parse($deadline)->format('d.m.Y') . ')');
                return back();
            }

            if (ApplicationRoster::where('application_id', $application->id)
                ->where('user_id', $player->id)
                ->exists()) {
                Toast::error('Игрок уже состоит в составе этой заявки');
                return back();
            }

            $fromApplication = $this->findActiveMembership($player->id, $tournament->id, $application->id);
            $hadHistory = $this->hasTournamentHistory($player->id, $tournament->id);

            $effectiveType = ($fromApplication || $hadHistory)
                ? RosterRequest::TYPE_TRANSFER
                : RosterRequest::TYPE_ADDITION;

            if ($effectiveType === RosterRequest::TYPE_TRANSFER && $tournament->transfers_limit !== null) {
                $used = RosterRequest::where('tournament_id', $tournament->id)
                    ->where('player_user_id', $player->id)
                    ->where('type', RosterRequest::TYPE_TRANSFER)
                    ->blocking()
                    ->count();

                if ($used >= $tournament->transfers_limit) {
                    Toast::error(sprintf(
                        'Лимит переходов исчерпан (%d из %d) для этого игрока в данном турнире',
                        $used,
                        $tournament->transfers_limit
                    ));
                    return back();
                }
            }

            $data += [
                'type' => $effectiveType,
                'to_application_id' => $application->id,
                'from_application_id' => $fromApplication?->id,
                'jersey_number' => $validated['jersey_number'],
                'position' => $validated['position'],
            ];
        }

        DB::transaction(fn () => RosterRequest::create($data));

        Toast::success('Запрос отправлен на рассмотрение');

        return redirect()->route('platform.roster.requests.list');
    }

    private function findActiveMembership(int $playerId, int $tournamentId, int $excludeApplicationId): ?TournamentApplication
    {
        return TournamentApplication::where('tournament_id', $tournamentId)
            ->where('status', 'approved')
            ->where('id', '!=', $excludeApplicationId)
            ->whereHas('roster', function ($q) use ($playerId) {
                $q->where('user_id', $playerId);
            })
            ->first();
    }

    private function hasTournamentHistory(int $playerId, int $tournamentId): bool
    {
        $removedRoster = ApplicationRoster::onlyTrashed()
            ->where('user_id', $playerId)
            ->whereHas('application', fn ($q) => $q->where('tournament_id', $tournamentId))
            ->exists();

        $approvedRequests = RosterRequest::where('tournament_id', $tournamentId)
            ->where('player_user_id', $playerId)
            ->whereIn('type', [RosterRequest::TYPE_TRANSFER, RosterRequest::TYPE_REMOVAL])
            ->where('status', RosterRequest::STATUS_APPROVED)
            ->exists();

        return $removedRoster || $approvedRequests;
    }
}

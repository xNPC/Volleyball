<?php

namespace App\Orchid\Screens\Application;

use App\Models\ApplicationRoster;
use App\Models\TournamentApplication;
use App\Models\User;
use App\Models\Venue;
use App\Orchid\Layouts\Application\AddPlayerLayout;
use App\Orchid\Layouts\Application\TournamentsListener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ApplicationEditScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(TournamentApplication $application): iterable
    {
        if (!$application->exists) {
            $draft = $this->draft();

            $application->tournament_id = $draft['tournament_id'];
            $application->team_id = $draft['team_id'];
            $application->venue_id = $draft['venue_id'];

            $roster = collect();
            foreach ($draft['roster'] as $index => $row) {
                $model = new ApplicationRoster();
                $model->id = $index;
                $model->user_id = $row['user_id'];
                $model->jersey_number = $row['jersey_number'];
                $model->position = $row['position'];
                $model->is_captain = $row['is_captain'] ?? false;
                $model->setRelation('player', User::find($row['user_id']));

                $roster->push($model);
            }

            $application->setRelation('roster', $roster);

            return [
                'application' => $application,
                'roster' => $roster,
            ];
        }

        return [
            'application' => $application,
            'roster' => $application->roster()
                ->orderByRaw('CAST(jersey_number AS UNSIGNED) ASC')
                ->get(),
        ];
    }

    private function draft(): array
    {
        $draft = array_merge([
            'tournament_id' => null,
            'team_id' => null,
            'venue_id' => null,
            'roster' => [],
        ], session('draft_application', []));

        if (auth()->check() && !collect($draft['roster'])->contains('user_id', auth()->id())) {
            array_unshift($draft['roster'], [
                'user_id' => auth()->id(),
                'jersey_number' => 1,
                'position' => 'outside',
                'is_captain' => true,
            ]);

            session(['draft_application' => $draft]);
        }

        return $draft;
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

    public $application;

    public function name(): ?string
    {
        return $this->application->exists ? 'Редактирование заявки' : 'Подача заявки';
    }

    public function permission(): ?iterable
    {
        return [
            //'platform.applications'
        ];
    }

    public function layout(): array
    {
        $canAddPlayer = request()->route()->getName() == 'platform.applications.create'
            || ($this->application->exists && (auth()->user()->hasAccess('platform.applications.edit') || !$this->application->is_complete));

        $rosterTitle = 'Состав';
        if ($this->application->exists) {
            $rosterTitle .= ' — ' . ($this->application->team->name ?? $this->application->tournament->name ?? '');
        }

        return [

            Layout::modal('editPlayer', [
                Layout::rows([

                    Input::make('roster.player.name')
                        ->title('Игрок')
                        ->disabled()
                        ->required(),
                    Input::make('roster.jersey_number')
                        ->title('Игровой номер')
                        ->min(1)
                        ->max(99)
                        ->required(),

                    Select::make('roster.position')
                        ->options(ApplicationRoster::POSITIONS)
                        ->title('Амплуа')
                        ->required(),
                ])
            ])
                ->applyButton('Сохранить')
                ->async('asyncGetPlayer')
                ->title('Редактирование игрока'),

            Layout::split([

                [

                    Layout::view('platform.application-steps'),

                    new TournamentsListener(),

                    Layout::rows([

                        Relation::make('application.venue_id')
                            ->fromModel(Venue::class, 'name')
                            ->displayAppend('display_name')
                            ->title('Домашний зал')
                            ->help('Обратите внимание! Поиск зала идет по названию, а не по адресу!')
                            ->allowEmpty()
                            ->required(),

                        Select::make('application.status')
                            ->title('Статус')
                            ->options(
                                TournamentApplication::STATUS,
                            )
                            ->canSee($this->application->exists and auth()->user()->hasAccess('platform.applications.edit')),

                        CheckBox::make('application.is_complete')
                            ->title('Отправить заявку на утверждение')
                            ->help('После этого состав изменить нельзя — только через дозаявки и отзаявки')
                            ->sendTrueOrFalse(),
                            //->disabled($this->application->is_complete),

                        Button::make('Сохранить')
                            ->icon('check')
                            ->type(Color::SUCCESS)
                            ->method('createOrUpdateApplication')
                            ->canSee(request()->route()->getName() == 'platform.applications.create'
                                or auth()->user()->hasAccess('platform.applications.edit')
                                or !$this->application->is_complete
                            ),

                        Button::make('Отменить')
                            ->icon('bs.x-circle')
                            ->type(Color::DEFAULT)
                            ->method('discardDraft')
                            ->confirm('Отменить создание заявки? Введённые данные будут потеряны')
                            ->novalidate()
                            ->canSee(request()->route()->getName() == 'platform.applications.create'
                                && session()->has('draft_application')
                            ),

                    ])
                ],
//                Layout::rows([
//                    //ApplicationScheduleLayout::class
//                ]),
//
                [
                    Layout::table('application.roster', [
                        TD::make('photo_preview', '')
                        ->render(fn($roster) =>
                        $roster->player->profile_photo_path
                            ? '<img src="' . asset('storage/' . $roster->player->profile_photo_path) . '" alt="Фото" class="" style="width: 40px; height: 40px; object-fit: cover;">'
                            : '<span class="badge bg-danger">X</span>'
                        )
                        ->alignCenter(),
TD::make('user_id', 'Ф.И.О.')
                        ->render(fn($user) =>
                        $user->player->name . ($user->is_captain ? ' <span class="badge bg-warning">Капитан</span>' : '')),
                    TD::make('birthday', 'Дата рождения')
                        ->render(function($roster) {
                            if (!$roster->player->birthday) {
                                return '<span class="badge bg-danger">X</span>';
                            }

                            $birthDate = \Carbon\Carbon::parse($roster->player->birthday);
                            $age = $birthDate->age;

                            return $birthDate->format('d.m.Y') . '<br><small class="text-muted">(' . $age . ' лет)</small>';
                    })
                    ->alignCenter(),
                    TD::make('jersey_number', 'Номер'),
                    TD::make('position', 'Амплуа')
                        ->render(function ($roster) {
                            $positions = ApplicationRoster::POSITIONS;
                            return $positions[$roster->position] ?? $roster->position;
                        }),


                    TD::make('actions', 'Действия')
                        ->render(fn ($roster)  =>
                            DropDown::make()
                                ->icon('bs.three-dots-vertical')
                                ->list([
                                    ModalToggle::make('Редактировать')
                                        ->modal('editPlayer')
                                        ->method('editPlayer')
                                        ->asyncParameters(['roster' => $roster->id])
                                        ->icon('pencil')
                                        ->canSee(request()->route()->getName() == 'platform.applications.create'
                                            or auth()->user()->hasAccess('platform.applications.edit')
                                            or !$this->application->is_complete
                                        ),

                                    Button::make('Удалить')
                                        ->icon('trash')
                                        ->method('removePlayer', ['id' => $roster->id])
                                        ->confirm('Вы уверены, что хотите удалить игрока из заявки?')
                                        ->novalidate()
                                        ->canSee(request()->route()->getName() == 'platform.applications.create'
                                            ? (int) ($roster->is_captain ?? 0) !== 1
                                            : (auth()->user()->hasAccess('platform.applications.edit')
                                                or !$this->application->is_complete)
                                        ),

                                    Link::make('Отзаявить')
                                        ->icon('bs.box-arrow-right')
                                        ->href(route('platform.roster.requests.create', ['application' => $this->application->id])
                                            . '?type=removal&player=' . $roster->user_id)
                                        ->canSee($this->application->exists),
                            ])
                        )
                ])
                        ->title($rosterTitle),

                    ...($canAddPlayer ? [AddPlayerLayout::class] : []),
                ],

            ])
            ->ratio('40/60'),

        ];
    }

    function createOrUpdateApplication(TournamentApplication $application, Request $request)
    {
        $applicationStatus = $request['application.status'] ?: 'pending';

        $validated = $request->validate([
            'application.tournament_id' => 'required|exists:tournaments,id',
            'application.team_id' => 'required|exists:teams,id',
            'application.venue_id' => 'required|exists:venues,id',
            'application.status' => 'nullable|in:pending,approved,rejected',
            'application.is_complete' => 'required',
        ]);

        //dd($applicationStatus);

        $appl = TournamentApplication::updateOrCreate([
                'id' => $request['application.id'],
            ],
            array_merge($validated['application'], [
                'status' => $applicationStatus
            ])
        );

        if ($appl->wasRecentlyCreated) {
            $draft = $this->draft();

            $hasCaptain = collect($draft['roster'])->contains('user_id', auth()->id());

            if (!$hasCaptain) {
                ApplicationRoster::create([
                    'application_id' => $appl->id,
                    'user_id' => auth()->id(),
                    'jersey_number' => 1,
                    'position' => 'outside',
                    'is_captain' => true,
                ]);
            }

            foreach ($draft['roster'] as $row) {
                ApplicationRoster::create([
                    'application_id' => $appl->id,
                    'user_id' => $row['user_id'],
                    'jersey_number' => $row['jersey_number'],
                    'position' => $row['position'],
                    'is_captain' => $row['is_captain'] ?? false,
                ]);
            }

            session()->forget('draft_application');
        }

        Toast::info('Успешно сохранено');

        return redirect()->route('platform.applications.edit', ['application' => $appl]);
    }

    public function addPlayer(TournamentApplication $application, Request $request)
    {
        $request->validate([
            'roster.user_id'       => 'required|exists:users,id',
            'roster.jersey_number' => 'required|integer|min:1|max:99',
            'roster.position'      => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $allowedPositions = ApplicationRoster::POSITIONS;

                    if (!array_key_exists($value, $allowedPositions)) {
                        $fail('Недопустимое значение для амплуа.');
                    }
                },
            ],

        ]);

        if (!$application->exists) {
            $data = $request->input('roster');

            $draft = $this->draft();
            $draft['tournament_id'] = $request->input('application.tournament_id');
            $draft['team_id'] = $request->input('application.team_id');
            $draft['venue_id'] = $request->input('application.venue_id');

            $exists = collect($draft['roster'])->contains('user_id', $data['user_id']);

            if ($exists) {
                Toast::error('Этот игрок уже добавлен в заявку');
                return back();
            }

            $draft['roster'][] = $data;
            session(['draft_application' => $draft]);

            Toast::info('Игрок успешно добавлен');

            return redirect()->route('platform.applications.create');
        }

        if ($application->is_complete && !auth()->user()->hasAccess('platform.applications.edit')) {
            abort(403, 'Заявка завершена и больше не может быть изменена.');
        }

        $data = $request->input('roster');
        $data['application_id'] = $application->id;

        // Проверка на дубликат игрока
        $exists = ApplicationRoster::where('application_id', $application->id)
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            Toast::error('Этот игрок уже добавлен в заявку');
            return back();
        }

        ApplicationRoster::create($data);

        Toast::info('Игрок успешно добавлен');

        return redirect()->route('platform.applications.edit', ['application' => $application]);
    }

    public function removePlayer(TournamentApplication $application, Request $request)
    {
        $rosterId = $request->query('id');

        if (!$application->exists) {
            $draft = $this->draft();

            if (!isset($draft['roster'][$rosterId])) {
                abort(404);
            }

            if (!empty($draft['roster'][$rosterId]['is_captain'])) {
                Toast::error('Капитан не может быть удален из заявки');
                return back();
            }

            unset($draft['roster'][$rosterId]);
            $draft['roster'] = array_values($draft['roster']);
            session(['draft_application' => $draft]);

            Toast::info('Игрок удален из заявки');

            return back();
        }

        ApplicationRoster::where('application_id', $application->id)
            ->where('id', $rosterId)
            ->delete();

        Toast::info('Игрок удален из заявки');

        return back();
    }

    public function editPlayer(TournamentApplication $application, Request $request)
    {
        $rosterId = $request->query('roster');

        $request->validate([
            'roster.jersey_number' => 'required|integer|min:1|max:99',
            'roster.position'      => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $allowedPositions = ApplicationRoster::POSITIONS;

                    if (!array_key_exists($value, $allowedPositions)) {
                        $fail('Недопустимое значение для амплуа.');
                    }
                },
            ],
        ]);

        if (!$application->exists) {
            $draft = $this->draft();

            if (!isset($draft['roster'][$rosterId])) {
                abort(404);
            }

            $draft['roster'][$rosterId]['jersey_number'] = $request->input('roster.jersey_number');
            $draft['roster'][$rosterId]['position'] = $request->input('roster.position');
            session(['draft_application' => $draft]);

            Toast::info('Игрок успешно обновлен');

            return back();
        }

        $roster = ApplicationRoster::where('application_id', $application->id)
            ->where('id', $rosterId)
            ->firstOrFail();

        $roster->update($request->input('roster'));

        Toast::info('Игрок успешно обновлен');

        return back();
    }

    public function asyncGetPlayer(Request $request): array
    {
        $rosterId = (int) $request->query('roster');

        if ($this->application->exists) {
            $roster = ApplicationRoster::where('application_id', $this->application->id)
                ->where('id', $rosterId)
                ->firstOrFail();
        } else {
            $draft = $this->draft();

            if (!isset($draft['roster'][$rosterId])) {
                abort(404);
            }

            $row = $draft['roster'][$rosterId];

            $roster = new ApplicationRoster();
            $roster->id = $rosterId;
            $roster->user_id = $row['user_id'];
            $roster->jersey_number = $row['jersey_number'];
            $roster->position = $row['position'];
            $roster->is_captain = $row['is_captain'] ?? false;
            $roster->setRelation('player', User::find($row['user_id']));
        }

        return [
            'roster' => $roster
        ];
    }

    public function discardDraft()
    {
        session()->forget('draft_application');

        Toast::info('Создание заявки отменено');

        return redirect()->route('platform.applications.list');
    }
}

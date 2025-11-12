<?php

namespace App\Orchid\Screens\Application;

use App\Models\ApplicationRoster;
use App\Models\TournamentApplication;
use App\Models\Venue;
use App\Orchid\Layouts\Application\AddPlayerLayout;
use App\Orchid\Layouts\Application\TournamentsListener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
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
        return [
            'application' => $application,
            'roster' => $application->roster()
                ->orderByRaw('CAST(jersey_number AS UNSIGNED) ASC')
                ->get(),
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

                ModalToggle::make('Добавить игрока')
                    ->modal('addPlayer')
                    ->method('addPlayer')
                    ->icon('plus')
                    ->canSee($this->application->exists and (auth()->user()->hasAccess('platform.applications.edit') or !$this->application->is_complete))
        ];
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
        return [

            Layout::modal('addPlayer', [
                AddPlayerLayout::class
            ])
                ->applyButton('Добавить игрока')
                ->async('asyncGetPlayer')
                ->title('Добавить игрока'),

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
                            ->title('Заявка завершена')
                            ->help('Будьте внимательны! Если Заявка будет завершена, вы не сможете больше ее изменять!')
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

                    ])
                ],
//                Layout::rows([
//                    //ApplicationScheduleLayout::class
//                ]),
//
                //Layout::rows([
                Layout::table('application.roster', [
                    TD::make('photo_preview', '')
                        ->render(fn($roster) =>
                        $roster->player->profile_photo_path
                            ? '<img src="' . asset('storage/' . $roster->player->profile_photo_path) . '" alt="Фото" class="" style="width: 40px; height: 40px; object-fit: cover;">'
                            : '<span class="badge bg-danger">X</span>'
                        )
                        ->alignCenter(),
                    TD::make('user_id', 'Ф.И.О.')
                        ->render(fn($user) => $user->player->name),
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
                                        ->canSee(request()->route()->getName() == 'platform.applications.create'
                                            or auth()->user()->hasAccess('platform.applications.edit')
                                            or !$this->application->is_complete
                                        ),
                            ])
                        )
                ])
                ->title('Состав'),

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

        return back();
    }

    public function removePlayer(TournamentApplication $application, Request $request)
    {
        $rosterId = $request->get('id');

        ApplicationRoster::where('application_id', $application->id)
            ->where('id', $rosterId)
            ->delete();

        Toast::info('Игрок удален из заявки');

        return back();
    }

    public function editPlayer(TournamentApplication $application, Request $request)
    {
        $rosterId = $request->get('roster');

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

        $roster = ApplicationRoster::where('application_id', $application->id)
            ->where('id', $rosterId)
            ->firstOrFail();

        $roster->update($request->input('roster'));

        Toast::info('Игрок успешно обновлен');

        return back();
    }

    public function asyncGetPlayer(ApplicationRoster $roster): array
    {
        return [
            'roster' => $roster
        ];
    }


}

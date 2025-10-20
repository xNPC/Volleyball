<?php

namespace App\Orchid\Screens\Application;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\User;
use App\Models\Venue;
use App\Orchid\Layouts\Application\TournamentsListener;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
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
            //'roster' => $application->roster()
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
            Button::make('Добавить игрока')
                ->icon('plus')
                ->method('test')
                ->canSee($this->application->exists)
        ];
    }

    public $application;

    public function name(): ?string
    {
        return $this->application->exists ? 'Редактирование заявки' : 'Подача заявки';
    }

    public function layout(): array
    {
        return [
            Layout::columns([

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
                            ->canSee($this->application->exists),

                        CheckBox::make('application.is_complete')
                            ->title('Заявка завершена')
                            ->help('Будьте внимательны! Если Заявка будет завершена, вы не сможете больше ее изменять!')
                            ->sendTrueOrFalse()
                            ->canSee(!$this->application->is_complete),

                        Button::make('Сохранить')
                            ->icon('check')
                            ->type(Color::SUCCESS)
                            ->method('createOrUpdateApplication'),

                    ])
                ],
//                Layout::rows([
//                    //ApplicationScheduleLayout::class
//                ]),
//
                //Layout::rows([
                Layout::table('application.roster', [
                    TD::make('user_id', 'Ф.И.О.')
                        ->render(fn($user) => $user->player->name),
                    TD::make('jersey_number', 'Номер'),
                    TD::make('position', 'Амплуа'),
                ])
                ->title('Состав'),

            ]),

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
}

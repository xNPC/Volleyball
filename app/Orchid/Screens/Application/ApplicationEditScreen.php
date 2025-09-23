<?php

namespace App\Orchid\Screens\Application;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\User;
use App\Models\Venue;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;

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
            'application' => $application
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
//            Button::make('Сохранить')
//                ->icon('check')
//                ->type(Color::PRIMARY)
//                ->method('test')
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
            //Layout::columns([
                Layout::rows([
                    Relation::make('application.tournament_id')
                        ->fromModel(Tournament::class, 'name')
                        ->applyScope('active')
                        //->value($this->application->tournament)
                        ->title('Турнир')
                        ->required(),

                    Relation::make('application.team_id')
                        ->fromModel(Team::class, 'name')
                        ->title('Команда')
                        ->required(),

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
                            TournamentApplication::STATUS
                        ),

                    CheckBox::make('application.is_complete')
                        ->title('Заявка завершена')
                        ->help('Будьте внимательны! Если Заявка будет завершена, вы не сможете больше ее изменять!')
                        ->sendTrueOrFalse(),
                ]),

//                Layout::rows([
//                    //ApplicationScheduleLayout::class
//                ]),
//
                Layout::rows([
                    Matrix::make('application.team')
                        ->columns([
                            'Фамилия Имя Отчество' => 'name',
                            'Номер' => 'jersey_number',
                            'Амплуа' => 'position',
                        ])
                        ->fields([
                            'name' => Relation::make( 'name')
                                ->fromModel(User::class, 'name')
                        ]),
                    Button::make('Сохранить')
                        ->icon('check')
                        ->type(Color::PRIMARY)
                        ->method('test')
                ]),
            //])
        ];
    }

    function test()
    {
        dd($this->application);
    }
}

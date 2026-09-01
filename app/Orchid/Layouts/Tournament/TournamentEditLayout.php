<?php

namespace App\Orchid\Layouts\Tournament;

use App\Models\Organization;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Color;

class TournamentEditLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title = 'Турнир';

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): array
    {
        return [
            Input::make('tournament.name')
                ->title('Название турнира')
                ->required(),

            Select::make('tournament.organization_id')
                ->fromQuery(Organization::query(), 'name')
                ->title('Организация')
                ->required()
                ->help('Выберите организацию, проводящую турнир'),

            Select::make('tournament.volleyball_type')
                ->title('Тип волейбола')
                ->options([
                    'indoor' => 'Классический (до 5 партий)',
                    'beach' => 'Пляжный (до 3 партий)',
                ])
                ->required(),

            TextArea::make('tournament.description')
                ->title('Описание')
                ->rows(4),

            Group::make([
                DateTimer::make('tournament.start_date')
                    ->title('Дата начала')
                    ->required()
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    //->format('d.m.Y')
                    ->enableTime(false)
                    ->placeholder('Выберите дату'),

                DateTimer::make('tournament.end_date')
                    ->title('Дата окончания')
                    ->required()
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    //->format('d.m.Y')
                    ->placeholder('Выберите дату'),
            ])
                ->autoWidth(),

            Select::make('tournament.status')
                ->options([
                    'planned' => 'Запланирован',
                    'ongoing' => 'В процессе',
                    'completed' => 'Завершён'
                ])
                ->title('Статус')
                ->required(),

            Group::make([
                DateTimer::make('tournament.addition_deadline_male')
                    ->title('Дедлайн дозаявок (муж.)')
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    ->placeholder('Без ограничения'),

                DateTimer::make('tournament.addition_deadline_female')
                    ->title('Дедлайн дозаявок (жен.)')
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    ->placeholder('Без ограничения'),
            ])
                ->autoWidth(),

            Group::make([
                DateTimer::make('tournament.transfer_deadline_male')
                    ->title('Дедлайн переходов (муж.)')
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    ->placeholder('Без ограничения'),

                DateTimer::make('tournament.transfer_deadline_female')
                    ->title('Дедлайн переходов (жен.)')
                    ->altFormat('d.m.Y')
                    ->allowInput()
                    ->placeholder('Без ограничения'),
            ])
                ->autoWidth(),

            Input::make('tournament.transfers_limit')
                ->type('number')
                ->title('Лимит переходов игрока')
                ->min(0)
                ->value(1)
                ->help('Сколько раз игрок может перейти в рамках турнира. Пусто — без ограничений.'),

            Button::make('Сохранить')
                ->icon('check')
                ->method('save')
                ->type(Color::PRIMARY)
        ];
    }
}

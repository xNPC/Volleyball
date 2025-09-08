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

            TextArea::make('tournament.description')
                ->title('Описание')
                ->rows(4),

            Group::make([
                DateTimer::make('tournament.start_date')
                    ->title('Дата начала')
                    ->required()
                    ->format('Y-m-d')
                    ->placeholder('Выберите дату'),

                DateTimer::make('tournament.end_date')
                    ->title('Дата окончания')
                    ->required()
                    ->format('Y-m-d')
                    ->placeholder('Выберите дату'),
            ])
                ->autoWidth(),

            Select::make('tournament.status')
                ->options([
                    'planned' => 'Запланирован',
                    'ongoing' => 'В процессе',
                    'completed' => 'Завершен'
                ])
                ->title('Статус')
                ->required(),

            Button::make('Сохранить')
                ->icon('check')
                ->method('save')
                ->type(Color::PRIMARY)
        ];
    }
}

<?php

namespace App\Orchid\Layouts\Application;

use App\Models\ApplicationRoster;
use App\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Color;

class AddPlayerLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        $draftUserIds = empty($this->query->get('application.id'))
            ? collect(session('draft_application', [])['roster'] ?? [])->pluck('user_id')->all()
            : [];

        return [
            Relation::make('roster.user_id')
                ->fromModel(User::class, 'name')
                ->applyScope('forSearch', $this->query->get('application.tournament_id'), $draftUserIds)
                ->title('Игрок')
                ->help('Показаны только игроки, ещё не заявленные за другие команды этого турнира'),


            Group::make([
                Input::make('roster.jersey_number')
                    ->title('Игровой номер')
                    ->min(1)
                    ->max(99),

                Select::make('roster.position')
                    ->options(ApplicationRoster::POSITIONS)
                    ->title('Амплуа'),
            ])
            ->widthColumns('30% 65%'),

            Button::make('Добавить игрока')
                ->method('addPlayer')
                ->icon('plus')
                ->type(Color::SUCCESS)
                ->block()
                ->novalidate(),

        ];
    }
}

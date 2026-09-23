<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use App\Models\User;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class UserEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('user.name')
                ->type('text')
                ->max(255)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('user.email')
                ->type('email')
                ->required()
                ->title(__('Email'))
                ->placeholder(__('Email')),

            Group::make([
                Select::make('user.gender')
                    ->options(User::GENDERS)
                    ->empty('Не указан')
                    ->required()
                    ->title('Пол')
                    ->help('Необходим для применения дедлайнов дозаявок и переходов'),

                DateTimer::make('user.birthday')
                    ->title('Дата рождения')
                    ->altFormat('d.m.Y')
                    ->allowInput(),
            ])
                ->autoWidth(),

            Input::make('user.phone')
                ->type('text')
                ->title('Телефон'),
        ];
    }
}

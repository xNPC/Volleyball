<?php

namespace App\Orchid\Layouts\Team;

use App\Models\Team;
use App\Models\User;
use App\Orchid\Screens\Team\TeamEditScreen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Color;

class TeamListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    public function __construct(protected $target)
    {

    }

    public function isSee(): bool
    {
        if ($this->target === 'teams') {
            return auth()->user()->hasAccess('platform.teams.edit');
        }

        if ($this->target === 'user_teams') {
            // Всегда показываем таблицу "Мои команды"
            return true;
        }

        return false;
    }

    public function iconNotFound(): string
    {
        return 'emoji-frown';
    }
    public function textNotFound(): string
    {
        return 'Упс...';
    }
    public function subNotFound(): string
    {
        return 'Доступные для Вас команды отсутствуют.';
    }

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Название'),

            TD::make('captain.name', 'Капитан'),

            TD::make('Действия')
                ->render(fn (Team $team) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Редактировать')
                            ->route('platform.teams.edit', $team)
                            ->icon('pencil'),
                        Button::make('Удалить')
                            ->method('remove', [
                                'team' => $team
                            ])
                            ->icon('trash')
                            ->canSee(auth()->user()->hasAccess('platform.teams.delete'))
                        ->confirm('Вместе с командой удалится вся ее история - заявки, игры и т.д., Вы точно хотите удалить команду?')
                    ])
                ),
        ];
    }
}

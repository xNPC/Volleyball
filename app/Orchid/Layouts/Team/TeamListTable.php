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
            TD::make('logo', '')
                ->width('60px')
                ->alignCenter()
                ->render(function (Team $team) {
                    $style = 'width: 40px; height: 40px;';

                    if ($team->logo_url) {
                        return '<img src="' . e($team->logo_url) . '" alt="" style="' . $style . ' object-fit: cover;">';
                    }

                    $initials = \Illuminate\Support\Str::of($team->name)->trim()->substr(0, 2)->upper();

                    return '<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-dark text-white fw-bold" style="' . $style . '">' . e($initials) . '</span>';
                }),

            TD::make('name', 'Название')
                ->sort()
                ->render(function (Team $team) {
                    $duplicateIds = $this->query->get('duplicate_team_ids', []);
                    $badge = in_array($team->id, $duplicateIds, true)
                        ? ' <span class="badge bg-danger ms-1">дубликат</span>'
                        : '';

                    return e($team->name) . $badge;
                }),

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

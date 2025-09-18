<?php

namespace App\Orchid\Layouts\Team;

use App\Models\Team;
use Orchid\Screen\Actions\Button;
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
    //protected $target = 'teams';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    public function __construct(protected $target)
    {

    }

//    public function isSee(): bool
//    {
//        return auth()->user()->hasAccess('platform.teams.edit');
//    }

    protected function columns(): iterable
    {
        return [
            TD::make('name', 'Название'),
            TD::make('Действия')
                ->render(function (Team $team) {
                    return
                        Group::make([
                            Button::make('Редактировать')
                                ->icon('pencil')
                                ->type(Color::PRIMARY),
                            Button::make('Удалить')
                                ->icon('trash')
                                ->type(Color::DANGER)
                        ])->autoWidth();
                })
        ];
    }
}

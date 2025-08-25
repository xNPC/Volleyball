<?php

namespace App\Orchid\Layouts\Tournament;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class StageListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'stages';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [

            TD::make('name','Название')
                ->render(function ($stage) {
                    return Link::make($stage->name)
                        ->route('platform.tournaments.edit', $stage)
                        ->class('d-block text-decoration-none text-reset py-2');
                }),
            TD::make('stage_type', 'Тип')
                ->render(function ($stages) {
                    return $stages->getStageTypeNameAttribute();
                    }
                )
                ->width('100px'),
            TD::make('start_date', 'Дата начала')
                ->render(function ($stages) {
                    return $stages->start_date->format('d.m.Y');
                })
                ->width('100px'),
            TD::make('end_date', 'Дата окончания')
                ->render(function ($stages) {
                    return $stages->end_date->format('d.m.Y');
                })
                ->width('100px'),
            TD::make('order','Порядок')
                ->width('50px')

        ];
    }
}

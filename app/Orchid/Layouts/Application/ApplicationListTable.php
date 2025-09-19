<?php

namespace App\Orchid\Layouts\Application;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ApplicationListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'applications';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('tournament_id', 'Tournament ID'),
            TD::make('team_id', 'Team ID'),
            TD::make('venue_id', 'Venue ID'),
            TD::make('status', 'Status'),
            TD::make('is_complete', 'Is Complete'),
            TD::make('created_at', 'Created at'),
            TD::make('updated_at', 'Updated at'),
        ];
    }
}

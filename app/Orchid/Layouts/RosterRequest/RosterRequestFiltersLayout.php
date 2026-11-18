<?php

namespace App\Orchid\Layouts\RosterRequest;

use App\Orchid\Filters\RosterRequestStatusFilter;
use App\Orchid\Filters\RosterRequestTournamentFilter;
use App\Orchid\Filters\RosterRequestTypeFilter;
use Orchid\Screen\Layouts\Selection;

class RosterRequestFiltersLayout extends Selection
{
    public function filters(): iterable
    {
        return [
            RosterRequestTypeFilter::class,
            RosterRequestTournamentFilter::class,
            RosterRequestStatusFilter::class,
        ];
    }
}

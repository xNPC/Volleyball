<?php

namespace App\Orchid\Layouts\Application;

use App\Orchid\Filters\ApplicationCompleteFilter;
use App\Orchid\Filters\ApplicationStatusFilter;
use App\Orchid\Filters\ApplicationTournamentFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class ApplicationFiltersLayout extends Selection
{
    public function filters(): iterable
    {
        return [
            ApplicationTournamentFilter::class,
            ApplicationStatusFilter::class,
            ApplicationCompleteFilter::class,
        ];
    }
}
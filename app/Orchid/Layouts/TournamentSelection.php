<?php

namespace App\Orchid\Layouts;

use App\Orchid\Filters\OrganizationFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class TournamentSelection extends Selection
{
    /**
     * @return Filter[]
     */
    public function filters(): iterable
    {
        return [
            OrganizationFilter::class,
        ];
    }
}

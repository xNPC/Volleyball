<?php

namespace App\Orchid\Screens\Tournament;

use App\Models\Organization;
use App\Models\Tournament;
use App\Orchid\Filters\OrganizationFilter;
use App\Orchid\Layouts\TournamentSelection;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Layouts\Table;

class TournamentListScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tournaments' => Tournament::with('organization')
                ->filtersApplySelection(TournamentSelection::class)
                //->filters()
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    public function filters(): array
    {
        return [
            OrganizationFilter::class
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список турниров';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать турнир')
                ->icon('plus')
                ->route('platform.tournaments.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [

            TournamentSelection::class,

            Layout::table('tournaments', [

                    TD::make('id', 'ID')
                            ->sort(),

                    TD::make('name', 'Название')
                        ->render(function (Tournament $tournament) {
                            return Link::make($tournament->name)
                                ->route('platform.tournaments.edit', $tournament);
                        }),

                    TD::make('organization.name', 'Организация'),

                    TD::make('start_date', 'Дата начала')
                        ->sort()
                        ->render(function ($tournament) {
                            return $tournament->start_date->format('d.m.Y');
                        }),

                    TD::make('status', 'Статус')
                        ->sort()
                        ->render(function ($tournament) {
                            return $tournament::STATUS[$tournament->status];
                        }
                    )
            ])
        ];
    }
}

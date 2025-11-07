<?php

namespace App\Orchid\Screens\Tournament;


use App\Models\Tournament;
use App\Models\Venue;
use App\Orchid\Filters\OrganizationFilter;
use App\Orchid\Layouts\TournamentSelection;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;


class TournamentListScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */

    public function permission(): ?iterable
    {
        return [
            'platform.tournaments'
        ];
    }

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
                    }),

                TD::make('Действия')
                    ->render(fn (Tournament $tournament) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->icon('bs.pencil')
                                ->route('platform.tournaments.edit', $tournament),

                            Button::make('Удалить')
                                ->icon('bs.trash')
                                ->method('remove', [
                                        'tournament' => $tournament]
                                )
                                ->confirm('После удаления, будут так же удалены все этапы и группы от этого турнира, что, Вы уверены, что хотите удалить турнир?')
                        ])
                    )

            ])
        ];
    }

    public function remove(Tournament $tournament)
    {
        $tournament->delete();

        Toast::info('Успешно удалено');
    }
}

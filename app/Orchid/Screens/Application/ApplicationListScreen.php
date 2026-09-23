<?php

namespace App\Orchid\Screens\Application;

use App\Models\TournamentApplication;
use App\Orchid\Layouts\Application\ApplicationFiltersLayout;
use App\Orchid\Layouts\Application\ApplicationListTable;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class ApplicationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = auth()->user();

        $base = TournamentApplication::query()
            ->with('tournament', 'team', 'venue')
            ->filters()
            ->filtersApplySelection(ApplicationFiltersLayout::class);

        // Если есть разрешение platform.applications - показываем все заявки
        if ($user->hasAccess('platform.applications.edit')) {
            $applications = $base->paginate();
        }
        // Иначе показываем только заявки, где пользователь является капитаном команды
        else {
            $applications = $base
                ->whereHas('team', function($query) use ($user) {
                    $query->where('captain_id', $user->id);
                })
                ->paginate();
        }

        return [
            'applications' => $applications
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список заявок';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать заявку')
                ->icon('plus')
                ->route('platform.applications.create'),
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
            ApplicationFiltersLayout::class,
            ApplicationListTable::class
        ];
    }

    public function remove(TournamentApplication $application)
    {
        $application->delete();

        Toast::info('Успешно удалено');
    }
}

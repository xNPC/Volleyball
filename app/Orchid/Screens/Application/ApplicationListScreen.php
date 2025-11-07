<?php

namespace App\Orchid\Screens\Application;

use App\Models\TournamentApplication;
use App\Orchid\Layouts\Application\ApplicationListTable;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layout;
use Orchid\Screen\Screen;

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

        // Если есть разрешение platform.applications - показываем все заявки
        if ($user->hasAccess('platform.applications.edit')) {
            $applications = TournamentApplication::with('tournament', 'team', 'venue')->get();
        }
        // Иначе показываем только заявки, где пользователь является капитаном команды
        else {
            $applications = TournamentApplication::with('tournament', 'team', 'venue')
                ->whereHas('team', function($query) use ($user) {
                    $query->where('captain_id', $user->id);
                })
                ->get();
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
            ApplicationListTable::class
        ];
    }
}

<?php

namespace App\Orchid\Screens\Team;

use App\Models\Team;
use App\Orchid\Layouts\Team\TeamListTable;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TeamListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $teams = collect();
        $userTeams = Team::with('captain')->where('captain_id', auth()->user()->id)->get();

        if (auth()->user()->hasAccess('platform.teams.edit')) {
            $teams = Team::with('captain')->paginate();
        }

        return [
            'teams' => $teams,
            'user_teams' => $userTeams,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список команд';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Создать команду')
                ->icon('plus')
                ->route('platform.teams.create')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $tabs['Мои команды'] = new TeamListTable('user_teams');

        if (auth()->user()->hasAccess('platform.teams.edit'))
            $tabs['Все команды'] = new TeamListTable('teams');


        return [
            Layout::tabs($tabs),
        ];
    }

    public function remove(Team $team)
    {
        // Проверяем права: либо пользователь имеет доступ к удалению, либо это его команда
        if (!auth()->user()->hasAccess('platform.teams.delete') &&
            $team->captain_id !== auth()->user()->id) {
            abort(403, 'У вас нет прав для удаления этой команды');
        }

        $team->delete();

        Toast::info('Команда успешно удалена');

        return redirect()->route('platform.teams.list');
    }
}

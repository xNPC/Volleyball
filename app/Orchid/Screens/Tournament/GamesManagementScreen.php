<?php

namespace App\Orchid\Screens\Tournament;

use App\Orchid\Layouts\Listeners\UpdateStageGroupsListener;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class GamesManagementScreen extends Screen
{
    public function permission(): ?iterable
    {
        return [
            'platform.games.edit',
            'platform.games.result'
        ];
    }
    /**
     * Query data.
     */
    public function query(Request $request): iterable
    {
        return [
            'tournament_id' => $request->get('tournament_id'),
            'stage_id' => $request->get('stage_id'),
            'group_id' => $request->get('group_id'),
        ];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Управление играми турниров';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            new UpdateStageGroupsListener(),
        ];
    }
}

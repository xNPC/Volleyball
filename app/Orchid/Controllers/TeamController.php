<?php
namespace App\Orchid\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController
{
    public function save(Request $request, Team $team)
    {
        $teamData = $request->input('team');
        $team->fill($teamData)->save();

        // Обновляем состав
        $team->members()->delete();
        foreach ($request->input('team.members', []) as $memberData) {
            $team->members()->create($memberData);
        }

        // Обновляем капитана
        if ($captain = $team->members()->where('is_captain', true)->first()) {
            $team->update(['captain_id' => $captain->user_id]);
        }

        return redirect()->route('platform.teams.list');
    }
}

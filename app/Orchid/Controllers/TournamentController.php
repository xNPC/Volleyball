<?php
namespace App\Orchid\Controllers;

use App\Models\Tournament;
use App\Models\TournamentStage;
use Illuminate\Http\Request;

class TournamentController
{
    public function save(Request $request, Tournament $tournament)
    {
        $tournamentData = $request->input('tournament');
        $tournament->fill($tournamentData)->save();

        // Сохраняем этапы
        foreach ($request->input('tournament.stages', []) as $stageData) {
            $stage = $tournament->stages()->updateOrCreate(
                ['id' => $stageData['id'] ?? null],
                $stageData
            );
        }

        return redirect()->route('platform.tournaments.list');
    }
}

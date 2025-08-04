<?php
namespace App\Orchid\Controllers;

use App\Models\TournamentApplication;
use Illuminate\Http\Request;

class ApplicationController
{
    public function save(Request $request, TournamentApplication $application)
    {
        $application->fill($request->input('application'))->save();

        // Сохраняем расписание
        $application->schedules()->delete();
        foreach ($request->input('application.schedules', []) as $schedule) {
            $application->schedules()->create($schedule);
        }

        // Сохраняем состав
        $application->roster()->delete();
        foreach ($request->input('application.roster', []) as $player) {
            $application->roster()->create($player);
        }

        return redirect()->route('platform.applications.list');
    }
}

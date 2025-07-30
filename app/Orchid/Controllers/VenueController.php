<?php
namespace App\Orchid\Controllers;

use App\Models\Venue;
use App\Models\VenueSchedule;
use Illuminate\Http\Request;

class VenueController
{
    public function save(Request $request, Venue $venue)
    {
        $venueData = $request->input('venue');
        $venue->fill($venueData)->save();

        // Сохраняем расписание
        foreach ($request->input('schedules', []) as $day => $schedule) {
            VenueSchedule::updateOrCreate(
                ['venue_id' => $venue->id, 'day_of_week' => $day],
                $schedule
            );
        }

        return redirect()->route('platform.venues.list');
    }
}

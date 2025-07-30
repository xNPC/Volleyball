<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class VenueSchedule extends Model
{
    use AsSource;

    protected $fillable = [
        'venue_id', 'day_of_week',
        'start_time', 'end_time', 'is_available'
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}

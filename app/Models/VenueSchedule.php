<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

class VenueSchedule extends Model
{
    use AsSource, softDeletes;

    protected $fillable = [
        'venue_id', 'day_of_week',
        'start_time', 'end_time', 'is_available'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}

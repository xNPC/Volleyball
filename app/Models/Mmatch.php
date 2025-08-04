<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Mmatch extends Model
{
    use AsSource;

    protected $fillable = [
        'stage_id', 'group_id', 'home_application_id',
        'away_application_id', 'venue_id', 'scheduled_time',
        'status', 'home_score', 'away_score',
        'first_referee_id', 'second_referee_id'
    ];

    public function homeTeam()
    {
        return $this->belongsTo(TournamentApplication::class, 'home_application_id');
    }

    public function sets()
    {
        return $this->hasMany(MatchSet::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}

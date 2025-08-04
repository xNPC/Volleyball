<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TournamentApplication extends Model
{
    use AsSource;

    protected $fillable = [
        'tournament_id', 'team_id', 'venue_id',
        'status', 'is_complete'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function schedules()
    {
        return $this->hasMany(ApplicationSchedule::class);
    }

    public function roster()
    {
        return $this->hasMany(ApplicationRoster::class);
    }
}

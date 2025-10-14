<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use AsSource, SoftDeletes;

    protected $fillable = [
        'name', 'logo',
        'description', 'captain_id'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function activeMembers()
    {
        return $this->members()->whereNull('leave_date');
    }

    public function applications()
    {
        return $this->hasMany(TournamentApplication::class);
    }

    public function scopeUserTeamsWithoutApplication($query, $tournamentId)
    {
        return $query
                    ->where('captain_id', auth()->user()->id)
                    ->whereDoesntHave('applications', function ($subQuery) use ($tournamentId) {
                        $subQuery->where('tournament_id', $tournamentId);
                    });
    }
}

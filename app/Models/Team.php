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

    public function groups()
    {
        return $this->belongsToMany(StageGroup::class, 'group_teams', 'team_id', 'group_id')
            ->withPivot('position')
            ->withTimestamps();
    }

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

    public function scopeWithApprovedApplicationForTournament($query, $tournamentId)
    {
        return $query->whereHas('applications', function ($q) use ($tournamentId) {
            $q->where('tournament_id', $tournamentId)
                ->where('status', 'approved');
        });
    }

    public function scopeInGroupWithApprovedApplication($query, $groupId, $tournamentId)
    {
        return $query->whereHas('applications', function ($q) use ($groupId, $tournamentId) {
            $q->where('tournament_id', $tournamentId)
                ->where('status', 'approved')
                ->whereExists(function ($query) use ($groupId) {
                    $query->selectRaw(1)
                        ->from('group_teams')
                        ->whereColumn('group_teams.application_id', 'tournament_applications.id')
                        ->where('group_teams.group_id', $groupId);
                });
        });
    }


}

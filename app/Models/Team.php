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

    /**
     * Группы, в которых состоит команда
     */
    public function stageGroups()
    {
        return $this->belongsToMany(StageGroup::class, 'stage_group_team')
            ->withTimestamps();
    }

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

    /**
     * Участники команды через заявки
     */
    public function members()
    {
        return $this->hasManyThrough(
            User::class,
            ApplicationRoster::class,
            'team_id', // Внешний ключ в application_rosters
            'id', // Внешний ключ в users
            'id', // Локальный ключ в teams
            'user_id' // Локальный ключ в application_rosters
        )->distinct();
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
        return $query->whereHas('tournamentApplications', function($q) use ($tournamentId) {
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

    /**
     * Турниры, в которых участвует команда
     */
    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_applications', 'team_id', 'tournament_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Активные турниры (где заявка принята)
     */
    public function activeTournaments()
    {
        return $this->tournaments()->wherePivot('status', 'approved');
    }

    /**
     * Турнирные заявки команды
     */
    public function tournamentApplications()
    {
        return $this->hasMany(TournamentApplication::class);
    }

    public function gamesAsHome()
    {
        return $this->hasManyThrough(
            Game::class,
            TournamentApplication::class,
            'team_id',
            'home_application_id',
            'id',
            'id'
        );
    }

    public function gamesAsAway()
    {
        return $this->hasManyThrough(
            Game::class,
            TournamentApplication::class,
            'team_id',
            'away_application_id',
            'id',
            'id'
        );
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TournamentApplication extends Model
{
    use AsSource, SoftDeletes;

    protected $fillable = [
        'tournament_id', 'team_id', 'venue_id',
        'status', 'is_complete'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public const STATUS = [
        'pending' => 'В ожидании',
        'approved' => 'Утверждена',
        'rejected' => 'Отклонена'
    ];

    public const IS_COMPLETE = [
        0 => 'Заполняется',
        1 => 'Завершена'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function players()
    {
        return $this->belongsToMany(
            User::class,
            'application_rosters',
            'application_id',
            'user_id'
        )
            ->withPivot('jersey_number', 'position', 'is_captain');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function schedules()
    {
        return $this->hasMany(ApplicationSchedule::class);
    }

    public function roster()
    {
        return $this->hasMany(ApplicationRoster::class, 'application_id')
            ->orderByRaw('CAST(jersey_number AS UNSIGNED) ASC');
    }

    public function groups()
    {
        return $this->belongsToMany(
            StageGroup::class,
            'group_teams',
            'application_id',
            'group_id'
        )->withPivot('position');
    }

    /**
     * Scope для approved заявок
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope для заявок конкретного турнира
     */
    public function scopeForTournament($query, $tournamentId)
    {
        return $query->where('tournament_id', $tournamentId);
    }

    /**
     * Scope для заявок, не находящихся в группах определенного этапа
     */
    public function scopeNotInStageGroups($query, $stageId)
    {
        return $query->whereDoesntHave('groups', function ($q) use ($stageId) {
            $q->where('stage_id', $stageId);
        });
    }

    /**
     * Scope для заявок, не находящихся в конкретной группе
     */
    public function scopeNotInGroup($query, $groupId)
    {
        return $query->whereDoesntHave('groups', function ($q) use ($groupId) {
            $q->where('stage_groups.id', $groupId);
        });
    }

    public function homeGames()
    {
        return $this->hasMany(Game::class, 'home_application_id');
    }

    public function awayGames()
    {
        return $this->hasMany(Game::class, 'away_application_id');
    }

    public function games()
    {
        return $this->homeGames->merge($this->awayGames);
    }
}

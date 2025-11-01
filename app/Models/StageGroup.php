<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class StageGroup extends Model
{
    use AsSource, Sortable, SoftDeletes;


    protected $fillable = [
        'stage_id',
        'name',
        'order',
        'team_count',
        'is_active'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function stage()
    {
        return $this->belongsTo(TournamentStage::class, 'stage_id', 'id');
    }

    public function teams()
    {
        return $this->belongsToMany(
            TournamentApplication::class,
            'group_teams',
            'group_id',
            'application_id'
        )->withPivot('position');
    }

    public function matches()
    {
        return $this->hasMany(Mmatch::class);
    }

    /**
     * Команды через заявки
     */
    public function teamApplications()
    {
        return $this->hasManyThrough(
            TournamentApplication::class,
            GroupTeam::class,
            'group_id', // Внешний ключ в group_teams
            'id', // Внешний ключ в tournament_applications
            'id', // Локальный ключ в stage_groups
            'application_id' // Локальный ключ в group_teams
        );
    }
}

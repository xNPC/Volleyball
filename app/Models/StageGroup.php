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
}

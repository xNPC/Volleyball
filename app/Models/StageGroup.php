<?php

namespace App\Models;

use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class StageGroup extends Model
{
    use AsSource;
    use Sortable;

    protected $fillable = [
        'stage_id',
        'name',
        'order',
        'team_count',
        'is_active'
    ];

    public function stage()
    {
        return $this->belongsTo(TournamentStage::class);
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

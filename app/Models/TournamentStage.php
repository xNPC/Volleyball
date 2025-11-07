<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TournamentStage extends Model
{
    use AsSource, Sortable, SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['groups'];

    protected $fetchMethod = 'get';

    protected $fillable = [
        'tournament_id', 'name', 'stage_type',
        'order', 'start_date', 'end_date'
    ];

    protected $casts = [
        'configuration' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function groups()
    {
        return $this->hasMany(StageGroup::class, 'stage_id', 'id')->orderBy('order');
    }

    public function getStageTypeNameAttribute()
    {
        return [
            'group' => 'Групповой',
            'playoff' => 'Плейофф',
            'qualification' => 'Квалификация'
        ][$this->stage_type] ?? $this->stage_type;
    }

    public function buildGroups()
    {
        if ($this->stage_type !== 'group') return;

        $teamCount = $this->tournament->applications()->count();
        $optimalGroupCount = $this->calculateOptimalGroups($teamCount);

        for ($i = 1; $i <= $optimalGroupCount; $i++) {
            $this->groups()->firstOrCreate([
                'name' => "Группа $i",
                'order' => $i,
                'team_count' => ceil($teamCount / $optimalGroupCount)
            ]);
        }
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'stage_id');
    }

}

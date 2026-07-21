<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayoffConfig extends Model
{
    protected $fillable = [
        'stage_id',
        'group_id',
        'total_teams',
        'bye_positions',
        'reverse_seeding',
        'match_format', // 'single' или 'best_of_3'
    ];

    protected $casts = [
        'bye_positions' => 'array',
        'reverse_seeding' => 'boolean',
        'match_format' => 'string',
    ];

    public function stage()
    {
        return $this->belongsTo(TournamentStage::class);
    }

    public function group()
    {
        return $this->belongsTo(StageGroup::class);
    }
}

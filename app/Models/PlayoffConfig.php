<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayoffConfig extends Model
{
    protected $table = 'playoff_configs';

    protected $fillable = [
        'stage_id',
        'group_id',
        'format_type',
        'total_teams',
        'bracket_structure',
        'rounds_config',
        'seeding_rules',
        'matchups',
    ];

    protected $casts = [
        'bracket_structure' => 'array',
        'rounds_config' => 'array',
        'seeding_rules' => 'array',
        'matchups' => 'array',
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

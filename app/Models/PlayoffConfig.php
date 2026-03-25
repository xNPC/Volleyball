<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayoffConfig extends Model
{
    protected $fillable = [
        'stage_id',
        'group_id',
        'total_teams',
        'bye_positions',     // массив позиций, которые проходят сразу в следующий раунд
        'reverse_seeding',   // обратный посев
    ];

    protected $casts = [
        'bye_positions' => 'array',
        'reverse_seeding' => 'boolean',
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

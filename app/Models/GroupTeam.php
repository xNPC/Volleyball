<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupTeam extends Model
{

    protected $fillable = [
        'group_id',
        'team_id',
        'position',
    ];

    public function group()
    {
        return $this->belongsTo(StageGroup::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

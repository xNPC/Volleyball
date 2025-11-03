<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupTeam extends Model
{
    protected $table = 'group_teams'; // Явно указываем таблицу

    protected $fillable = [
        'group_id',
        'application_id', // Добавляем это поле
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

    public function application()
    {
        return $this->belongsTo(TournamentApplication::class, 'application_id');
    }
}

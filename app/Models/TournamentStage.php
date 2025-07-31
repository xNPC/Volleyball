<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TournamentStage extends Model
{
    use AsSource;

    protected $fillable = [
        'tournament_id', 'name', 'stage_type',
        'order', 'start_date', 'end_date'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function groups()
    {
        return $this->hasMany(StageGroup::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSet extends Model
{
    protected $fillable = [
        'game_id',
        'set_number',
        'home_score',
        'away_score',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'stage_id',
        'group_id',
        'home_application_id',
        'away_application_id',
        'venue_id',
        'scheduled_time',
        'status',
        'home_score',
        'away_score',
        'first_referee_id',
        'second_referee_id',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
    ];

    const STATUSES = [
        'scheduled' => 'Запланирована',
        'live' => 'В прямом эфире',
        'completed' => 'Завершена',
        'cancelled' => 'Отменена',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TournamentStage::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StageGroup::class, 'group_id', 'id');
    }

    public function homeApplication(): BelongsTo
    {
        return $this->belongsTo(TournamentApplication::class, 'home_application_id');
    }

    public function awayApplication(): BelongsTo
    {
        return $this->belongsTo(TournamentApplication::class, 'away_application_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function firstReferee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_referee_id');
    }

    public function secondReferee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_referee_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(GameSet::class);
    }

    public function getHomeTeamAttribute()
    {
        return $this->homeApplication->team;
    }

    public function getAwayTeamAttribute()
    {
        return $this->awayApplication->team;
    }

    public function getScoreAttribute(): string
    {
        if ($this->home_score === null || $this->away_score === null) {
            return 'vs';
        }

        return "{$this->home_score} - {$this->away_score}";
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}

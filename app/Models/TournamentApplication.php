<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TournamentApplication extends Model
{
    use AsSource, SoftDeletes;

    protected $fillable = [
        'tournament_id', 'team_id', 'venue_id',
        'status', 'is_complete'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public const STATUS = [
        'pending' => 'В ожидании',
        'approved' => 'Утверждена',
        'rejected' => 'Отклонена'
    ];

    public const IS_COMPLETE = [
        0 => 'Заполняется',
        1 => 'Завершена'
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function schedules()
    {
        return $this->hasMany(ApplicationSchedule::class);
    }

    public function roster()
    {
        return $this->hasMany(ApplicationRoster::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

class ApplicationRoster extends Model
{
    use AsSource, SoftDeletes;

    protected $table = 'application_rosters';

    protected $fillable = [
        'application_id',
        'user_id',
        'jersey_number',
        'position',
        'is_captain'
    ];

    protected $dates = [
        'deleted_at'
    ];

    protected $casts = [
        'is_captain' => 'boolean'
    ];

    public const POSITIONS = [
        'libero' => 'Либеро',
        'setter' => 'Связующий',
        'outside' => 'Доигровщик',
        'middle' => 'Центральный блокирующий',
        'opposite' => 'Диагональный'
    ];

    public function application()
    {
        return $this->belongsTo(TournamentApplication::class);
    }

    public function tournamentApplication()
    {
        return $this->belongsTo(TournamentApplication::class);
    }

    /**
     * Пользователь
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tournament()
    {
        return $this->hasOneThrough(
            Tournament::class,
            TournamentApplication::class,
            'id', // Foreign key on applications table
            'id', // Foreign key on tournaments table
            'application_id', // Local key on rosters table
            'tournament_id' // Local key on applications table
        );
    }
}

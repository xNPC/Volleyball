<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use AsSource, SoftDeletes;

    protected $fillable = [
        'team_id', 'user_id', 'jersey_number',
        'position', 'is_captain', 'join_date', 'leave_date'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

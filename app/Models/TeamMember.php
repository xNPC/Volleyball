<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use AsSource;

    protected $fillable = [
        'team_id', 'user_id', 'jersey_number',
        'position', 'is_captain', 'join_date', 'leave_date'
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

<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use AsSource;

    protected $fillable = [
        'name', 'logo', 'city',
        'description', 'captain_id'
    ];

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function activeMembers()
    {
        return $this->members()->whereNull('leave_date');
    }
}

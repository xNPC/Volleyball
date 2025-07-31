<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use AsSource;

    protected $fillable = [
        'organization_id', 'name', 'description',
        'start_date', 'end_date', 'status', 'rules'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function stages()
    {
        return $this->hasMany(TournamentStage::class);
    }
}

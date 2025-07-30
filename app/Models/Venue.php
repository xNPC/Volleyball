<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Venue extends Model
{
    use AsSource;

    protected $fillable = [
        'organization_id', 'name', 'address',
        'city', 'capacity', 'contact_phone'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

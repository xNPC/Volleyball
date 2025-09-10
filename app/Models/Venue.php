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
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(VenueSchedule::class);
    }

    // Хелперы
    public function getWorkingDaysAttribute()
    {
        return [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье'
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Organization extends Model {

    use AsSource, Filterable, Attachable, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'logo',
        'contact_email', 'contact_phone'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}

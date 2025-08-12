<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Orchid\Filters\TournamentStatusFilter;

class Tournament extends Model
{
    use AsSource, Filterable;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'rules'
    ];

    protected $allowedFilters = [
        'name',
        'status',
        'start_date',
        'organization_id'
    ];

    protected $allowedSorts = [
        'name',
        'start_date',
        'status',
        'created_at'
    ];

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function stages()
    {
        return $this->hasMany(TournamentStage::class);
    }

    public function applications()
    {
        return $this->hasMany(TournamentApplication::class);
    }
}

<?php

namespace App\Models;

use App\Orchid\Filters\OrganizationFilter;
use Orchid\Filters\Types\Like;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    public const STATUS = [
        'planned' => 'Запланирован',
        'ongoing' => 'В процессе',
        'completed' => 'Завершен'
    ];

    protected $allowedFilters = [
        //'organization_id',
        OrganizationFilter::class
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'name',
        'start_date',
        'status',
        'organization_id'
    ];

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

    public function url() {
        return route('platform.tournaments.edit', $this->id);
    }
}

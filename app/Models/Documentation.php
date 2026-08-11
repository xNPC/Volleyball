<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Documentation extends Model
{
    use AsSource, Attachable, Sortable;

    protected $fillable = ['title', 'content', 'status', 'order'];

    protected $casts = [
        'status' => 'boolean'
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;

class Photo extends Model
{
    use AsSource;

    protected $fillable = ['album_id', 'original_name', 'filename', 'thumbnail', 'path', 'sort_order'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail);
    }
}

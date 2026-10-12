<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\AsSource;

class Broadcast extends Model
{
    use AsSource, Attachable;

    protected $fillable = [
        'tournament_id',
        'game_id',
        'title',
        'description',
        'vk_url',
        'embed_url',
        'embed_code',
        'preview_image',
        'scheduled_at',
        'started_at',
        'ended_at',
        'status',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const STATUSES = [
        'scheduled' => 'Запланирована',
        'live' => 'В эфире',
        'finished' => 'Завершена',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(Attachment::class, 'id', 'preview_image');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>=', now());
            });
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if (! $this->preview_image) {
            return null;
        }

        if ($this->relationLoaded('cover') && $this->cover) {
            return $this->cover->url();
        }

        $attachment = $this->attachments()->find($this->preview_image);

        return $attachment ? $attachment->url() : null;
    }

    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'live';
    }

    /**
     * Ссылка на встроенный плеер ВК.
     */
    public function getEmbedSrcAttribute(): ?string
    {
        if ($this->embed_url) {
            return $this->embed_url;
        }

        return $this->buildEmbedFromUrl($this->vk_url);
    }

    public function getHasEmbedAttribute(): bool
    {
        return (bool) ($this->embed_src || $this->embed_code || $this->vk_url);
    }

    protected function buildEmbedFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~video(-?\d+)_(\d+)~', $url, $matches)) {
            return 'https://vk.com/video_ext.php?oid=' . $matches[1] . '&id=' . $matches[2] . '&hd=2';
        }

        return null;
    }
}

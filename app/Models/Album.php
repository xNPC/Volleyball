<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\AsSource;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Album extends Model
{
    use AsSource, Attachable;

    protected $fillable = ['title', 'slug', 'description', 'cover_image', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $withCount = ['photos'];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);
            }
        });
    }

    public function getCoverUrlAttribute()
    {
        // 1. Если обложка выбрана вручную и связь 'cover' подгружена
        if ($this->cover_image && $this->relationLoaded('cover') && $this->cover) {
            return $this->cover->url();
        }

        // 2. Ищем строго по ID обложки (на случай, если связь 'cover' не была подгружена в контроллере)
        if ($this->cover_image) {
            // Используем find(), чтобы сделать точечный запрос по первичному ключу таблицы Orchid
            $attachment = $this->attachments()->find($this->cover_image);
            if ($attachment) {
                return $attachment->url();
            }
        }

        // 3. Если обложки нет, но загружены фото в альбом — берем миниатюру первого фото
        // Используем relationLoaded для оптимизации, либо обычную проверку
        if ($this->photos && $this->photos->isNotEmpty()) {
            return $this->photos->first()->thumbnail_url;
        }

        // 4. Полный фолбэк — стандартная картинка-заглушка
        return asset('images/placeholder.jpg');
    }

    public function getStoragePathAttribute(): string
    {
        return 'gallery/' . $this->slug;
    }

    public function getAbsolutePathAttribute(): string
    {
        return storage_path('app/public/' . $this->storage_path);
    }

    /**
     * Создаем связь для обложки альбома
     */
    public function cover(): HasOne
    {
        // Ищем вложение по типу 'album_cover'
        return $this->hasOne(Attachment::class, 'id', 'cover_image');
    }

    // Добавьте в конец модели Album:
    protected static function booted()
    {
        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);
            }

            // Проверяем уникальность slug при создании
            $originalSlug = $album->slug;
            $counter = 1;

            while (static::where('slug', $album->slug)->exists()) {
                $album->slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        });
    }
}

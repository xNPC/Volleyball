<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class LegalDocument extends Model
{
    use AsSource;

    public const CONSENT = 'consent';

    public const TERMS = 'terms';

    public const SLUGS = [
        self::CONSENT,
        self::TERMS,
    ];

    protected $fillable = ['slug', 'title', 'content'];

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Orchid\Platform\Models\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'birthday',
        'gender',
    ];

//    protected $dates = [
//        'birthday',
//        'deleted_at',
//        'created_at',
//        'updated_at'
//    ];

    public const GENDERS = [
        'male' => 'Мужской',
        'female' => 'Женский',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'permissions',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'birthday' => 'date:Y-m-d',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id'         => Where::class,
        'name'       => Like::class,
        'email'      => Like::class,
        'updated_at' => WhereDateStartEnd::class,
        'created_at' => WhereDateStartEnd::class,
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'updated_at',
        'created_at',
    ];

    /**
     * Составы заявок, где участвует пользователь
     */
    public function applicationRosters()
    {
        return $this->hasMany(ApplicationRoster::class);
    }

    public function scopeForSearch($query, $tournamentId = null, $excludedUserIds = [])
    {
        if ($tournamentId) {
            $query->whereDoesntHave('applicationRosters', function ($q) use ($tournamentId) {
                $q->whereNull('deleted_at')
                    ->whereHas('application', fn ($q2) => $q2->where('tournament_id', $tournamentId));
            });
        }

        if (!empty($excludedUserIds)) {
            $query->whereNotIn('id', $excludedUserIds);
        }

        return $query;
    }

    /**
     * Турнирные заявки, в которых участвует пользователь
     */
    public function tournamentApplications()
    {
        return $this->hasManyThrough(
            TournamentApplication::class,
            ApplicationRoster::class,
            'user_id', // Внешний ключ в application_rosters
            'id', // Внешний ключ в tournament_applications
            'id', // Локальный ключ в users
            'application_id' // Локальный ключ в application_rosters
        );
    }

    /**
     * Подтвержденные турнирные заявки пользователя
     */
    public function approvedTournamentApplications()
    {
        return $this->tournamentApplications()->where('status', 'approved');
    }

    /**
     * Команды пользователя через заявки
     */
    public function teams()
    {
        return $this->hasManyThrough(
            Team::class,
            TournamentApplication::class,
            'id', // Внешний ключ в tournament_applications
            'id', // Внешний ключ в teams
            'id', // Локальный ключ в users
            'team_id' // Локальный ключ в tournament_applications
        )->where('tournament_applications.status', 'approved')
            ->distinct();
    }

    protected static function booted(): void
    {
        static::updated(function (User $user) {
            if (!$user->wasChanged('profile_photo_path')) {
                return;
            }

            $service = app(ImageService::class);

            if ($user->profile_photo_path) {
                $service->profilePhotoThumbnail($user);
            } else {
                $service->deleteProfileThumbnail($user);
            }
        });
    }

    /**
     * URL миниатюры профильного фото (генерируется при отсутствии)
     */
    protected function profilePhotoThumbUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (!$this->profile_photo_path) {
                return null;
            }

            $path = app(ImageService::class)->profilePhotoThumbnail($this);

            if (!$path) {
                return null;
            }

            return Storage::disk('public')->url($path) . '?v=' . md5($this->profile_photo_path);
        });
    }
}

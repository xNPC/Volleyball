<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Screen\AsSource;

class RosterRequest extends Model
{
    use AsSource, SoftDeletes;

    protected $fillable = [
        'type',
        'tournament_id',
        'player_user_id',
        'to_application_id',
        'from_application_id',
        'jersey_number',
        'position',
        'comment',
        'rejection_reason',
        'status',
        'created_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const TYPE_ADDITION = 'addition';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_REMOVAL = 'removal';

    public const TYPES = [
        self::TYPE_ADDITION => 'Дозаявка',
        self::TYPE_TRANSFER => 'Переход',
        self::TYPE_REMOVAL => 'Отзаявка',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'На рассмотрении',
        self::STATUS_APPROVED => 'Утверждена',
        self::STATUS_REJECTED => 'Отклонена',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'player_user_id');
    }

    public function toApplication()
    {
        return $this->belongsTo(TournamentApplication::class, 'to_application_id');
    }

    public function fromApplication()
    {
        return $this->belongsTo(TournamentApplication::class, 'from_application_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Блокирующие статусы для повторных действий (pending/approved)
     */
    public function scopeBlocking($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPES = [
        'seminar',
        'conference',
        'education',
        'camp',
        'competition',
        'course',
        'workshop',
        'mentorship',
        'volunteering',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'description',
        'image',
        'type',
        'application_deadline',
        'start_date',
        'end_date',
        'location',
        'is_online',
        'max_participants',
        'price',
        'prerequisites',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'application_deadline' => 'datetime',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_online' => 'boolean',
            'price' => 'decimal:2',
            'prerequisites' => 'array',
        ];
    }

    protected $appends = ['average_rating'];

    public function nvo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'call_category');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_calls')->withTimestamps();
    }

    public function getAverageRatingAttribute(): float
    {
        // Prefer the aggregate produced by withAvg() to avoid N+1 queries.
        if (array_key_exists('feedbacks_avg_rating', $this->attributes)) {
            return round((float) $this->attributes['feedbacks_avg_rating'], 1);
        }

        if ($this->relationLoaded('feedbacks')) {
            return round((float) $this->feedbacks->avg('rating'), 1);
        }

        return 0.0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}

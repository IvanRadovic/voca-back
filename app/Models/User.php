<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_YOUTH = 'youth';
    public const ROLE_NVO = 'nvo';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'date_of_birth',
        'city',
        'education_level',
        'avatar',
        'bio',
        'headline',
        'about',
        'education',
        'work_experience',
        'skills',
        'linkedin',
        'phone',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    /** Age in years, or null when date of birth is unknown. */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? (int) $this->date_of_birth->age : null;
    }

    public function isNvo(): bool
    {
        return $this->role === self::ROLE_NVO;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Organization profile (only for nvo accounts). */
    public function nvo(): HasOne
    {
        return $this->hasOne(Nvo::class);
    }

    /** Calls published by this NVO. */
    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    /** Interests of a youth user (shared taxonomy with call categories). */
    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** Wishlist. */
    public function savedCalls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class, 'saved_calls')->withTimestamps();
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}

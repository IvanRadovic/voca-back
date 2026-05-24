<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mentor extends Model
{
    protected $fillable = [
        'name', 'title', 'title_en', 'bio', 'bio_en', 'expertise', 'avatar', 'email', 'linkedin', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MentorshipRequest::class);
    }
}

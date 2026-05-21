<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug'];

    public $timestamps = true;

    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class, 'call_category');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user');
    }
}

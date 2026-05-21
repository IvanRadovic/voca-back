<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Single taxonomy used both for call categories and user interests.
     */
    public const NAMES = [
        'IT',
        'Programming',
        'Design',
        'Marketing',
        'Entrepreneurship',
        'Business',
        'Sport',
        'Music',
        'Film',
        'Photography',
        'Writing',
        'Volunteering',
        'Travel',
        'Languages',
        'Psychology',
        'Ecology',
        'Fitness',
        'Art',
        'Personal Development',
        'Health',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}

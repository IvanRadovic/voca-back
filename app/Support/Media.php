<?php

namespace App\Support;

use Illuminate\Support\Str;

class Media
{
    /**
     * Resolve a stored media path to a URL. Absolute URLs (e.g. seeded stock
     * photos) are returned as-is; relative paths are served from public storage.
     */
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}

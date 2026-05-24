<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'call_id',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    /**
     * Issue (or fetch) a certificate for a user/call pair.
     */
    public static function issueFor(int $userId, int $callId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'call_id' => $callId],
            ['code' => static::generateCode(), 'issued_at' => now()]
        );
    }

    public static function generateCode(): string
    {
        do {
            $code = 'VOCA-'.strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }
}

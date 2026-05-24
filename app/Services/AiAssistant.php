<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Anthropic Messages API with a graceful offline
 * fallback so the feature degrades to templates when no key is configured
 * (or the API is unreachable).
 */
class AiAssistant
{
    public function enabled(): bool
    {
        return ! empty(config('services.anthropic.key'));
    }

    /**
     * Run a completion. Returns the text, or null on failure so callers
     * can fall back to a template.
     *
     * @param  array<int,array{role:string,content:string}>  $messages
     */
    public function complete(string $system, array $messages, int $maxTokens = 800): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model'),
                'max_tokens' => $maxTokens,
                'system' => $system,
                'messages' => $messages,
            ]);

            if ($response->failed()) {
                Log::warning('Anthropic API error', ['status' => $response->status()]);

                return null;
            }

            return $response->json('content.0.text');
        } catch (\Throwable $e) {
            Log::warning('Anthropic request failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}

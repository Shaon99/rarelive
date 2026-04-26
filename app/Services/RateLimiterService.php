<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

class RateLimiterService
{
    /**
     * Check if the request exceeds the rate limit.
     *
     * @param  string  $key  Unique rate limiter key
     * @param  int  $maxAttempts  Maximum allowed attempts
     * @param  int  $decaySeconds  Lockout time in seconds
     * @return array|null Returns error message with wait time if rate limit exceeded, otherwise null
     */
    public function checkRateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 5) // Decay time set to 10 seconds
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;

            return [
                'message' => "Too many attempts! Please try again in {$minutes} min {$remainingSeconds} sec.",
                'status' => 429,
            ];
        }

    }

    /**
     * Increment rate limit attempts.
     *
     * @param  string  $key  Unique rate limiter key
     * @param  int  $decaySeconds  Lockout time in seconds
     * @return void
     */
    public function hitRateLimit(string $key, int $decaySeconds = 600)
    {
        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Clear rate limit for a key.
     *
     * @param  string  $key  Unique rate limiter key
     * @return void
     */
    public function clearRateLimit(string $key)
    {
        RateLimiter::clear($key);
    }
}

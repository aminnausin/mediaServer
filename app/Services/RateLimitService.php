<?php

namespace App\Services;

use App\Data\Access\RateLimitData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitService {
    /**
     * @param  RateLimitData[]  $limits
     */
    public function getRateLimitError(array $limits): ?JsonResponse {
        foreach ($limits as $limit) {
            if (RateLimiter::tooManyAttempts($limit->key, $limit->maxAttempts)) {
                return response()->json([
                    'message' => "{$limit->message} Try again in " . ceil(RateLimiter::availableIn($limit->key) / 60) . ' minutes.',
                ], 429);
            }
        }

        return null;
    }

    /**
     * @param  RateLimitData[]  $limits
     */
    public function hitRateLimits(array $limits): void {
        foreach ($limits as $limit) {
            RateLimiter::hit($limit->key, $limit->decaySeconds);
        }
    }
}

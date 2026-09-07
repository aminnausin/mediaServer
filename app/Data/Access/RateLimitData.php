<?php

namespace App\Data\Access;

final readonly class RateLimitData {
    public function __construct(
        public string $key,
        public int $maxAttempts,
        public int $decaySeconds,
        public string $message,
    ) {}
}

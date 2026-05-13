<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ServerConfigValue implements CastsAttributes {
    public function get($model, string $key, mixed $value, array $attributes): mixed {
        return match ($attributes['type']) {
            'bool' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    public function set($model, string $key, mixed $value, array $attributes): mixed {
        return match ($attributes['type'] ?? null) {
            'array' => json_encode($value),
            default => $value,
        };
    }
}

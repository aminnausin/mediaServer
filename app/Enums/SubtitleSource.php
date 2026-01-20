<?php

namespace App\Enums;

enum SubtitleSource: string {
    case EMBEDDED = 'embedded';
    case EXTERNAL = 'external';

    public function makeKey(string|int $value): string {
        return "{$this->value}:{$value}";
    }
}

<?php

namespace App\Enums;

enum MediaType: int {
    case VIDEO = 0;
    case AUDIO = 1;

    public function isVideo(): bool {
        return $this === self::VIDEO;
    }

    public function isAudio(): bool {
        return $this === self::AUDIO;
    }

    public function label(): string {
        return strtolower($this->name);
    }

    public static function fromLabel(?string $label): ?self {
        return collect(self::cases())->first(fn (self $case) => $case->label() === strtolower((string) $label));
    }
}

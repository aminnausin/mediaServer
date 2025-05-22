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

    /**
     * Check if a given value is a valid enum case.
     */
    public static function isValid(int $type): bool {
        foreach (self::cases() as $case) {
            if ($case->value == $type) {
                return true;
            }
        }

        return false;
    }

    public static function getValues(): array {
        return array_column(self::cases(), 'value');
    }

    public static function getLabel(TaskStatus $type): string {
        return match ($type) {
            self::VIDEO => 'video',
            self::AUDIO => 'audio',
        };
    }
}

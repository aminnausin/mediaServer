<?php

namespace App\Enums;

enum TaskStatus: string {
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETE = 'complete';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case INCOMPLETE = 'incomplete';


    public function isPending(): bool {
        return $this === self::PENDING;
    }

    public function isProcessing(): bool {
        return $this === self::PROCESSING;
    }

    public function isComplete(): bool {
        return $this === self::PROCESSING;
    }

    public function isCancelled(): bool {
        return $this === self::PROCESSING;
    }

    public function isFailed(): bool {
        return $this === self::PROCESSING;
    }

    public function isIncomplete(): bool {
        return $this === self::INCOMPLETE;
    }

    /**
     * Check if a given value is a valid enum case.
     */
    public static function isValid(string $type): bool {
        foreach (self::cases() as $case) {
            if ($case->value === $type) {
                return true;
            }
        }

        return false;
    }

    public static function getValues(): array {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum TaskStatus: int {
    case PENDING = 0;
    case PROCESSING = 1;
    case INCOMPLETE = 2;
    case COMPLETED = 3;
    case CANCELLED = -1;
    case FAILED = -2;

    public function isPending(): bool {
        return $this === self::PENDING;
    }

    public function isProcessing(): bool {
        return $this === self::PROCESSING;
    }

    public function isComplete(): bool {
        return $this === self::COMPLETED;
    }

    public function isCancelled(): bool {
        return $this === self::CANCELLED;
    }

    public function isFailed(): bool {
        return $this === self::FAILED;
    }

    public function isIncomplete(): bool {
        return $this === self::INCOMPLETE;
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
            self::PENDING => 'pending',
            self::PROCESSING => 'processing',
            self::COMPLETED => 'completed',
            self::INCOMPLETE => 'incomplete',
            self::CANCELLED => 'cancelled',
            self::FAILED => 'failed',
        };
    }
}

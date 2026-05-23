<?php

namespace App\Enums;

enum TaskStatus: int {
    case PENDING = 0;
    case PROCESSING = 1;
    case INCOMPLETE = 2;
    case COMPLETED = 3;
    case CANCELLED = -1;
    case FAILED = -2;
    case SKIPPED = 4;

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

    public function label(): string {
        return strtolower($this->name);
    }
}

<?php

namespace App\Traits;

use App\Enums\TaskStatus;
use App\Models\Task;

trait EnsuresTaskIsStarted {
    public function ensureTaskIsStarted(int $taskId): bool {
        return Task::where('id', $taskId)
            ->where('status', TaskStatus::PENDING)
            ->whereNull('started_at')
            ->update([
                'status' => TaskStatus::PROCESSING,
                'started_at' => now(),
            ]) === 1;
    }
}

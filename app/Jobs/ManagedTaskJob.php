<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Services\TaskService;
use App\Traits\EnsuresTaskIsStarted;
use App\Traits\HasUpsert;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ManagedTaskJob implements ShouldQueue {
    use Batchable, Dispatchable, EnsuresTaskIsStarted, HasUpsert, InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;

    protected ?int $subTaskId = null;

    protected ?Carbon $startedAt = null;

    /**
     * Sets task to processing if not already set (atomic, also sets started at time)
     * Subtracts task pending
     *
     * Sets subtask started at time
     * Sets subtask to processing
     * Sets subtask starting summary
     */
    public function beginTask(TaskService $taskService, string $summary = ''): void {
        if ($this->batch()?->cancelled()) {
            // Determine if the batch has been cancelled...
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);
            $this->delete();

            return;
        }

        $this->ensureTaskIsStarted($this->taskId);

        $this->startedAt = now();

        $taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt, 'summary' => $summary]);
    }
}

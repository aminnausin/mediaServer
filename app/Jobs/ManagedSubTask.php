<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Task;
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
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

abstract class ManagedSubTask implements ShouldQueue {
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
    public function beginSubTask(TaskService $taskService, string $summary = ''): bool {
        if (! $this->taskId) {
            throw new LogicException('Task ID missing, cannot begin task');
        }

        if (! $this->subTaskId) {
            throw new LogicException('SubTask ID missing, cannot begin task');
        }

        if ($this->batch()?->cancelled()) {
            // Determine if the batch has been cancelled...
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);
            $this->delete();

            return false;
        }

        $this->ensureTaskIsStarted($this->taskId);
        $this->startedAt = now();

        DB::transaction(function () use ($taskService, $summary) {
            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt, 'summary' => $summary]);
        });

        return true;
    }

    public function completeSubTask(TaskService $taskService, string $summary = '', array $taskCountUpdates = ['sub_tasks_complete' => '++']): ?Task {
        $shouldBroadcastTaskUpdate = array_key_exists('sub_tasks_total', $taskCountUpdates);

        $endedAt = now();
        $duration = $this->getTaskDuration($endedAt);

        $subTaskUpdates = [
            'status' => TaskStatus::COMPLETED,
            'summary' => $summary,
            'progress' => 100,
            'ended_at' => $endedAt,
            'duration' => $duration,
        ];

        // TODO: This forces 100% completed state but this value should be calculated wait nevermind this is a subtask

        DB::transaction(function () use ($taskService, $taskCountUpdates, $shouldBroadcastTaskUpdate, $subTaskUpdates) {
            $taskService->updateTaskCounts($this->taskId, $taskCountUpdates, $shouldBroadcastTaskUpdate); // TODO: Move broadcasts outside of updates?
            $taskService->updateSubTask($this->subTaskId, $subTaskUpdates);
        });

        return Task::find($this->taskId);
    }

    public function failSubTask(TaskService $taskService, Throwable $th): void {
        $endedAt = now();
        $duration = $this->getTaskDuration($endedAt);
        $subTaskUpdates = ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration];

        DB::transaction(function () use ($taskService, $subTaskUpdates) {
            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $taskService->updateSubTask($this->subTaskId, $subTaskUpdates);
        });
    }

    private function getTaskDuration(Carbon $endedAt): int {
        $startedAt = $this->startedAt ?? $endedAt;

        return (int) $startedAt->diffInSeconds($endedAt);
    }
}

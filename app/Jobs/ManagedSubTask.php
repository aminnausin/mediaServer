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
use Illuminate\Support\Facades\Log;
use LogicException;
use Throwable;

abstract class ManagedSubTask implements ShouldQueue {
    use Batchable, Dispatchable, EnsuresTaskIsStarted, HasUpsert, InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;

    protected ?int $subTaskId = null;

    protected ?Carbon $startedAt = null;

    /**
     * Begin subtask execution
     *
     * Sets subtask started at time
     * Sets subtask to processing
     * Sets subtask starting summary
     *
     * Decrements pending subtask count from parent task
     *
     * @param  string  $summary  Starting summary
     * @return bool False if cancelled, true if should continue
     *
     * @throws LogicException if taskId or subTaskId not set
     */
    public function beginSubTask(TaskService $taskService, string $summary = ''): bool {
        if (! $this->taskId) {
            throw new LogicException('Task ID missing, cannot begin task');
        }

        if (! $this->subTaskId) {
            throw new LogicException('SubTask ID missing, cannot begin task');
        }

        if ($this->isCancelled()) {
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

    /**
     * Complete the subtask
     *
     * @param  string  $summary  completion summary
     * @param  array  $taskCountUpdates  Subtask count updates for parent task
     * @param  TaskStatus  $status  Manual subtask status (for marking as incomplete)
     * @return Task|null Parent task
     */
    public function completeSubTask(TaskService $taskService, string $summary = '', array $taskCountUpdates = ['sub_tasks_complete' => '++'], TaskStatus $status = TaskStatus::COMPLETED): ?Task {
        $shouldBroadcastTaskUpdate = array_key_exists('sub_tasks_total', $taskCountUpdates);

        $endedAt = now();
        $duration = $this->getTaskDuration($endedAt);

        $subTaskUpdates = [
            'status' => $status,
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

    /**
     * Fail the subtask
     *
     * @param  Throwable  $th  Failure exception
     */
    public function failSubTask(TaskService $taskService, Throwable $th): void {
        if (! $this->taskId || ! $this->subTaskId) {
            Log::error('Task failed before setup', [
                'job' => static::class,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return;
        }

        $endedAt = now();
        $duration = $this->getTaskDuration($endedAt);
        $subTaskUpdates = ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration];

        try {
            DB::transaction(function () use ($taskService, $subTaskUpdates) {
                $taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
                $taskService->updateSubTask($this->subTaskId, $subTaskUpdates);
            });
        } catch (Throwable $e) {
            Log::error('Task/Subtask update failed', [
                'task_id' => $this->taskId,
                'subtask_id' => $this->subTaskId,
                'original_error' => $th->getMessage(),
                'update_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine if the batch has been cancelled...
     */
    protected function isCancelled(): bool {
        return $this->batch()?->cancelled() ?? false;
    }

    /**
     * Calculate task duration in seconds
     *
     * @return int 0 if task never started
     */
    private function getTaskDuration(Carbon $endedAt): int {
        $startedAt = $this->startedAt ?? $endedAt;

        return (int) $startedAt->diffInSeconds($endedAt);
    }
}

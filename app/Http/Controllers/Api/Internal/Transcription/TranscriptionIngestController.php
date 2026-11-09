<?php

namespace App\Http\Controllers\Api\Internal\Transcription;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranscriptionRequest;
use App\Jobs\Transcription\IngestTranscript;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Models\Task;
use App\Services\FileJobService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TranscriptionIngestController extends Controller {
    public function __construct(protected TaskService $taskService, protected FileJobService $fileJobService) {}

    public function __invoke(
        StoreTranscriptionRequest $request,
        Metadata $metadata,
    ) {
        $data = $request->validated();
        try {
            $subTask = SubTask::with('task')
                ->where('task_id', $data['task_id'])
                ->where('reference_uuid', $metadata->uuid)
                ->where('reference_type', IngestTranscript::class)
                ->whereHas('task', function ($query) {
                    $query->whereIn('status', [
                        TaskStatus::PENDING,
                        TaskStatus::PROCESSING,
                    ]);
                })
                ->latest()
                ->first();

            abort_unless($subTask->status === TaskStatus::PENDING, 409, 'Transcript ingestion is not pending.');

            $this->fileJobService->ingestTranscript(
                data: [
                    'user_id' => $subTask->task->user_id,
                    'subtask_id' => $subTask->id,
                    'vtt' => $data['vtt'],
                    'language' => $data['language'],
                ],
                metadata: $metadata,
                taskId: (int) $data['task_id'],
            );

            return response()->noContent(202);
        } catch (\Throwable $th) {
            if ($data['task_id']) {
                $this->failTask((int) $data['task_id'], $th);
            }
            throw $th;
        }
    }

    private function failTask(int $taskId, Throwable $th): void {
        $endedAt = now();
        $error = 'Error: ' . ($th->getMessage() ?? 'Unknown');

        try {
            DB::transaction(function () use ($taskId, $endedAt, $error) {
                $subTask = SubTask::with('task')
                    ->where('task_id', $taskId)
                    ->where('reference_type', IngestTranscript::class)
                    ->where('status', TaskStatus::PENDING)
                    ->lockForUpdate()
                    ->first();

                if ($subTask) {
                    $this->taskService->updateTaskCounts($taskId, [
                        'sub_tasks_failed' => '++',
                    ]);

                    $this->taskService->updateSubTask($subTask->id, [
                        'status' => TaskStatus::FAILED,
                        'summary' => $error,
                        'ended_at' => $endedAt,
                        'duration' => $this->getTaskDuration($endedAt, $subTask->startedAt),
                    ]);
                }

                $task = $subTask ? $subTask->task : Task::find($taskId);

                $this->taskService->updateTask($taskId, [
                    'status' => TaskStatus::FAILED,
                    'summary' => $error,
                    'ended_at' => $endedAt,
                    'duration' => $this->getTaskDuration($endedAt, $task?->startedAt),
                ], true);
            });
        } catch (Throwable $e) {
            Log::error('Task/Subtask failure update failed', [
                'task_id' => $taskId,
                'original_error' => $error,
                'update_error' => $e->getMessage(),
            ]);
        }
    }

    private function getTaskDuration(Carbon $endedAt, ?Carbon $startedAt = null): int {
        return (int) ($startedAt ?? $endedAt)->diffInSeconds($endedAt);
    }
}

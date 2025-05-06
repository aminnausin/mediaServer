<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Services\PreviewGeneratorService;
use App\Services\TaskService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class GeneratePreviewImage implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subTaskId;

    protected $startedAt;

    protected $itemTitle;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public array $data,
        public string $path,
        public int $taskId,
    ) {
        $taskService = App::make(TaskService::class);
        $this->itemTitle = $data['title'] ?? $path;

        $subTask = $taskService->createSubTask(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => "Regenerate Preview Image for '{$this->itemTitle}'"]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(PreviewGeneratorService $previewGenerator, TaskService $taskService) {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        $this->startedAt = now();
        $taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt]);

        try {
            $result = $previewGenerator->generateImage($this->data, $this->path, true);
            if (! $result) {
                throw new Exception('Preview image generation failed. View logs for error.');
            }

            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);

            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            $taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => 'Generated preview image.',
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }
}

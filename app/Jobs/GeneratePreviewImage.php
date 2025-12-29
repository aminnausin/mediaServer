<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use App\Services\GenerateImageException;
use App\Services\PreviewGeneratorService;
use App\Services\TaskService;

class GeneratePreviewImage extends ManagedTaskJob {
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
        int $taskId,
    ) {
        $this->itemTitle = $data['title'] ?? $path;

        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => "Regenerate Preview Image for '{$this->itemTitle}'"]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(PreviewGeneratorService $previewGenerator, TaskService $taskService) {
        $this->beginTask($taskService);

        try {
            $result = $previewGenerator->generateImage($this->data, $this->path, true);
            if (! $result) {
                throw new GenerateImageException('Preview image generation failed. View logs for error.');
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

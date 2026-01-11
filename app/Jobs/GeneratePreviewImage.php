<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use App\Services\GenerateImageException;
use App\Services\PreviewGeneratorService;
use App\Services\TaskService;

class GeneratePreviewImage extends ManagedSubTask {
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
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $result = $previewGenerator->generateImage($this->data, $this->path, true);
            if (! $result) {
                throw new GenerateImageException('Preview image generation failed. View logs for error.');
            }
            $this->completeSubTask($taskService, 'Generated preview image.');
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }
}

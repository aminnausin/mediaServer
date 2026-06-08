<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use App\Services\GenerateImageException;
use App\Services\PreviewGeneratorService;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Model;

class GeneratePreviewImage extends ManagedSubTask {
    protected string $itemTitle;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public array $data,
        public Model $owner,
        int $taskId,
    ) {
        $this->itemTitle = $data['title'] ?? $owner->name ?? 'Unknown';

        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => "Regenerate Preview Image for '{$this->itemTitle}'"]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(PreviewGeneratorService $previewGenerator, TaskService $taskService) {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $image = $previewGenerator->generateAndPersist($this->owner, $this->data);
            if (! $image) {
                throw new GenerateImageException('Preview image generation failed. View logs for error.');
            }
            $this->completeSubTask($taskService, "Generated preview image at {$image->path}.");
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }
}

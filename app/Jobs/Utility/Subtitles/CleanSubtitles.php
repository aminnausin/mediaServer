<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use App\Services\TaskService;

class CleanSubtitles extends ManagedSubTask {
    public function __construct($taskId) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Clean Subtitles']);
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(TaskService $taskService): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->cleanSubtitles();
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function cleanSubtitles(): string {
        $deletedFiles = 0;
        $deletedRows = 0;

        // Unimplemented: Not sure what to implement anymore so this will be skipped for now

        return "Deleted {$deletedRows} subtitle row(s) and {$deletedFiles} file(s)";
    }
}

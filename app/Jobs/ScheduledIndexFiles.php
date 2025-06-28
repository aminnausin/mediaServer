<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Services\FileJobService;
use App\Services\TaskService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScheduledIndexFiles implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $controller;

    public function __construct() {
        $taskService = new TaskService;
        $this->controller = new FileJobService($taskService);
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $this->handleTask();
    }

    public function handleTask() {
        $name = 'Scheduled Index Files';
        $description = 'Looks for folder and video changes in in all Libraries.';

        return $this->controller->executeBatchOperation(
            userId: null,
            name: $name,
            description: $description,
            chain: function ($task) {
                return [
                    new SyncFiles($task->id),
                    new IndexFiles($task->id),
                ];
            },
        );
    }
}

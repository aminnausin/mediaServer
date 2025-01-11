<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Http\Controllers\DirectoryController;
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
        $this->controller = new DirectoryController($taskService);
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
        try {
            $task = $this->controller->setupTask(null, $name, $description, 2);
            $chain = [
                new SyncFiles($task->id),
                new IndexFiles($task->id),
            ];

            $batch = $this->controller->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id]);
            // return response()->json(['task_id' => $task->id, 'message' => 'Scheduled Task "INDEX FILES" was started.']);
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now(), 'summary' => $th->getMessage()]);
            }
            Log::error($th->getMessage());
            // return response()->json(['error' => 'Error cannot index files', 'details' => $th->getMessage()], 500);
        }
    }
}

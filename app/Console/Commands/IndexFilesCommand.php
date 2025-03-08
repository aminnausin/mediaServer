<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Http\Controllers\DirectoryController;
use App\Jobs\IndexFiles;
use App\Jobs\SyncFiles;
use App\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexFilesCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mediaServer:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Media Files';

    protected $controller;

    /**
     * Execute the console command.
     */
    public function handle() {
        $taskService = new TaskService;
        $this->controller = new DirectoryController($taskService);

        return $this->handleTask();
    }

    public function handleTask() {
        $name = 'Console Index Files';
        $description = 'Looks for folder and video changes in in all Libraries.';
        $this->info('Starting Index Files');
        try {
            $task = $this->controller->setupTask(null, $name, $description, 2);
            $chain = [
                new SyncFiles($task->id),
                new IndexFiles($task->id),
            ];
            $batch = $this->controller->setupBatch($chain, $task);
            $task->update(['batch_id' => $batch->id]);
            $this->info('Index Files Queued!');

            return true;
        } catch (\Throwable $th) {
            if ($task) {
                $task->update(['status' => TaskStatus::FAILED, 'ended_at' => now(), 'summary' => $th->getMessage()]);
            }
            Log::error($th->getMessage());
            $this->error($th->getMessage());

            return false;
        }
    }
}

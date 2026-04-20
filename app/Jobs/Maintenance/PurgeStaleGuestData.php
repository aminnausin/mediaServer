<?php

namespace App\Jobs\Maintenance;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Support\Facades\DB;

class PurgeStaleGuestData extends ManagedSubTask {
    private array $tables = [
        'playback_progress',
    ];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        int $taskId,
    ) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Purge Stale Guest Data']);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(TaskService $taskService) {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $expiry = now()->subDays(30);

            foreach ($this->tables as $table) {
                DB::table($table)
                    ->whereNotNull('guest_token')
                    ->where('updated_at', '<', $expiry)
                    ->delete();
            }

            $this->completeSubTask($taskService, 'Purged Expired Guest Data');
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }
}

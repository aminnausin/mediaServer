<?php

namespace App\Jobs\Utility\Paths;

use App\Enums\TaskStatus;
use App\Exceptions\DataLostException;
use App\Jobs\ManagedSubTask;
use App\Models\Folder;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Support\Facades\Storage;

class CleanFolderPaths extends ManagedSubTask {
    /**
     * Create a new job instance.
     */
    public function __construct(public $folders, $taskId) {
        //
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Sync Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->cleanFolderPaths($taskService);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function cleanFolderPaths(TaskService $taskService) {
        if (count($this->folders) == 0) {
            throw new DataLostException('Folder Data Lost');
        }

        $transactions = [];
        $error = false;
        foreach ($this->folders as $index => $folder) {
            try {
                $stored = [];
                $changes = [];

                $stored = $folder->toArray();

                if (strpos($stored['path'], '\\')) {
                    $newPath = str_replace('\\\\', '/', $stored['path']); // Replace double back-slashes first
                    $newPath = str_replace('\\', '/', $newPath); // Replace single back-slashes
                    $changes['path'] = $newPath;
                }

                if (! empty($changes)) {
                    array_push($transactions, [...$stored, ...$changes]);
                }

                $taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index + 1) / count($this->folders) * 100)]);
            } catch (\Throwable $th) {
                $error = true;
                $errorMessage = 'Error cannot clean folder path ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates';
                dump($errorMessage);

                throw new \Exception($errorMessage);
            }
        }

        if (empty($transactions) || $error) {
            return 'No Changes Found';
        }
        Folder::upsert($transactions, 'id', ['path']);

        $msg = 'Updated ' . count($transactions) . ' folder path(s) from id ' . ($transactions[0]['id']) . ' to ' . ($transactions[count($transactions) - 1]['id']);
        dump($msg);

        $dataCache = Storage::json('dataCache.json') ?? [];
        $dataCache[date('Y-m-d-h:i:sa')] = [
            'job' => 'cleanFolderPaths',
            'message' => $msg,
            'data' => $transactions,
        ];
        Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));

        return $msg;
    }
}

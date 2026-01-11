<?php

namespace App\Jobs\Utility\Paths;

use App\Enums\TaskStatus;
use App\Jobs\ManagedTask;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Support\Facades\Storage;

class CleanVideoPaths extends ManagedTask {
    /**
     * Create a new job instance.
     */
    public function __construct(public $videos, $taskId) {
        //
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Sync Files']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        if (! $this->beginTask($taskService)) {
            return;
        }

        try {
            $summary = $this->cleanVideoPaths($taskService);
            $this->completeTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failTask($taskService, $th);
            throw $th;
        }
    }

    private function cleanVideoPaths(TaskService $taskService) {
        if (count($this->videos) == 0) {
            throw new \Exception('Video Data Lost');
        }

        $transactions = [];
        $error = false;
        foreach ($this->videos as $index => $video) {
            try {
                $stored = [];
                $changes = [];

                $stored = $video->toArray();

                if (strpos($stored['path'], '\\')) {
                    $newPath = str_replace('\\\\', '/', $stored['path']); // Replace double back-slashes first
                    $newPath = str_replace('\\', '/', $newPath); // Replace single back-slashes
                    $changes['path'] = $newPath;
                }

                if (! empty($changes)) {
                    array_push($transactions, [...$stored, ...$changes]);
                }

                $taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index + 1) / count($this->videos) * 100)]);
            } catch (\Throwable $th) {
                $error = true;
                $errorMessage = 'Error cannot clean file path ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates';
                dump($errorMessage);

                throw new \Exception($errorMessage);
            }
        }
        if (empty($transactions) || $error) {
            return 'No Changes Found';
        }

        Video::upsert($transactions, 'id', ['path']);

        $msg = 'Updated ' . count($transactions) . ' video path(s) from id ' . ($transactions[0]['id']) . ' to ' . ($transactions[count($transactions) - 1]['id']);
        dump($msg);

        $dataCache = Storage::json('dataCache.json') ?? [];
        $dataCache[date('Y-m-d-h:i:sa')] = [
            'job' => 'cleanVideoPaths',
            'message' => $msg,
            'data' => $transactions,
        ];
        Storage::put('dataCache.json', json_encode($dataCache, JSON_UNESCAPED_SLASHES));

        return $msg;
    }
}

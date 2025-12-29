<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Support\Facades\Storage;

class CleanVideoPaths extends ManagedTaskJob {
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
        $this->beginTask($taskService);

        try {
            $summary = $this->cleanVideoPaths($taskService);
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            $taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
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

<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Folder;
use App\Models\SubTask;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanFolderPaths implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;
    protected $subTaskId;

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
    public function handle(): void {
        if ($this->batch() && $this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);
            return;
        }

        DB::table('tasks')->where('id', $this->taskId)->decrement('sub_tasks_pending');
        SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::PROCESSING, 'started_at' => now()]);

        try {
            $summary = $this->cleanFolderPaths();
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_complete');
            SubTask::where('id', $this->subTaskId)->update([
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'ended_at' => now(),
                'progress' => 100,
            ]);
        } catch (\Throwable $th) {
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_failed');
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::FAILED, 'summary' => "Error: " . $th->getMessage(), 'ended_at' => now()]);
        }
    }

    private function cleanFolderPaths() {
        if (count($this->folders) == 0) {
            throw new \Exception('Folder Data Lost');
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

                if (count($changes) > 0) {
                    array_push($transactions, [...$stored, ...$changes]);
                }

                SubTask::where('id', $this->subTaskId)->update(['progress' => (int) (($index + 1) / count($this->folders) * 100)]);
            } catch (\Throwable $th) {
                $error = true;
                $errorMessage = 'Error cannot clean folder path ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates';
                dump($errorMessage);

                throw new \Exception($errorMessage);
            }
        }

        if (count($transactions) == 0 || $error == true) {
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

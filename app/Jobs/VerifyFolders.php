<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Series;
use App\Models\SubTask;
use App\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class VerifyFolders implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;
    protected $subTaskId;


    /**
     * Create a new job instance.
     */
    public function __construct(public $folders, $taskId) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Verify ' . count($folders) . ' Folders']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);
            return;
        }

        DB::table('tasks')->where('id', $this->taskId)->decrement('sub_tasks_pending');
        SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::PROCESSING, 'started_at' => now()]);

        try {
            $summary = $this->VerifyFolders();
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

    private function VerifyFolders() {
        if (count($this->folders) == 0) {
            throw new \Exception('Folder Data Lost');
        }

        $transactions = [];
        $error = false;

        foreach ($this->folders as $index => $folder) {
            try {
                $stored = [];
                $changes = [];

                $series = Series::firstOrCreate(['composite_id' => $folder->path], ['folder_id' => $folder->id]);

                $stored = $series->toArray();

                if (is_null($series->episodes)) {
                    $changes['episodes'] = Video::where('folder_id', $folder->id)->count();
                }

                if (is_null($series->title)) {
                    $changes['title'] = $folder->name;
                }

                if (count($changes) > 0) {
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($folder->name);
                }

                SubTask::where('id', $this->subTaskId)->update(['progress' => (int) (($index + 1) / count($this->folders)) * 100]);

                // dump($series->toArray());
            } catch (\Throwable $th) {
                $errorMessage = 'Error cannot verify folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates and ' . count($this->folders) . ' checks';
                $error = true;

                dump($errorMessage);

                throw new \Exception($errorMessage);
            }
        }

        try {
            if (count($transactions) == 0 || $error == true) {
                return 'No Changes Found';
            }

            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes']);

            $summary = 'Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']);
            dump($summary);

            return $summary;
        } catch (\Throwable $th) {
            $errorMessage = 'Error cannot insert verified folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates'; // . [...$ids]);
            dump($errorMessage);

            throw new \Exception($errorMessage);
        }
    }
}

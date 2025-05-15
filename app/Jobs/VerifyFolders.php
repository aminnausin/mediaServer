<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Series;
use App\Models\SubTask;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifyFolders implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    protected $subTaskId;

    protected $startedAt;

    protected $taskService;

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
    public function handle(TaskService $taskService): void {
        $this->taskService = $taskService;

        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        $this->startedAt = now();
        $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt]);

        try {
            $summary = $this->verifyFolders();
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            $this->taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            throw $th;
        }
    }

    private function verifyFolders() {
        if (count($this->folders) == 0) {
            throw new \Exception('Folder Data Lost');
        }

        $transactions = [];
        $error = false;
        $index = 0;

        foreach ($this->folders as $folder) {
            try {
                $stored = [];
                $changes = [];

                $series = Series::firstOrCreate(['composite_id' => $folder->path], ['folder_id' => $folder->id]);

                $stored = $series->toArray();

                // if (is_null($series->episodes)) {
                $changes['episodes'] = Video::where('folder_id', $folder->id)->count();
                // }

                if (is_null($series->title)) {
                    $changes['title'] = $folder->name;
                }

                if (isset($series->thumbnail_url) && ! strpos($series->thumbnail_url, str_replace('http://', '', str_replace('https://', '', config('api.app_url'))))) {
                    $thumbnailResult = $this->getThumbnailAsFile($series->thumbnail_url, explode('/', $series->composite_id ?? 'unsorted/unsorted')[0] . '/' . basename($series->id));
                    if ($thumbnailResult) {
                        $changes['thumbnail_url'] = $thumbnailResult;
                        dump('got thumbnail for ' . $series->id . ' at ' . $thumbnailResult);
                    }
                }

                $totalSize = $series->folder->total_size;
                if ($stored['total_size'] !== $totalSize) {
                    $changes['total_size'] = $totalSize;
                }

                if (! empty($changes)) {
                    array_push($transactions, [...$stored, ...$changes]);
                    // dump([...$stored, ...$changes]);
                    // dump($changes);
                    // dump($folder->name);
                }

                $index += 1;
                $this->taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index / count($this->folders)) * 100)]);

                // dump($series->toArray());
            } catch (\Throwable $th) {
                $errorMessage = 'Error cannot verify folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates and ' . count($this->folders) . ' checks';
                $error = true;

                dump($errorMessage);

                throw new \Exception($errorMessage);
            }
        }

        try {
            if (empty($transactions) || $error) {
                return 'No Changes Found';
            }

            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes', 'thumbnail_url', 'total_size']);

            $summary = 'Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']);
            dump($summary);

            return $summary;
        } catch (\Throwable $th) {
            $errorMessage = 'Error cannot insert verified folder series data ' . $th->getMessage() . ' Cancelling ' . count($transactions) . ' updates'; // . [...$ids]);
            dump($errorMessage);

            throw new \Exception($errorMessage);
        }
    }

    private function getThumbnailAsFile($url, $compositePath) {
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                dump('Getting thumbnail');
                $imageContent = $response->body();
                $path = 'thumbnails/' . $compositePath . '.webp';
                Storage::disk('public')->put($path, $imageContent);

                return VerifyFiles::getPathUrl($path);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::warning('Unable to download thumbnail image from ' . $url . ' : ' . $th->getMessage());
        }

        return false;
    }
}

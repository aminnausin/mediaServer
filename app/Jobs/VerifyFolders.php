<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Exceptions\DataLostException;
use App\Models\Series;
use App\Models\SubTask;
use App\Services\TaskService;
use App\Traits\HasUpsert;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifyFolders implements ShouldQueue {
    use Batchable, Dispatchable, HasUpsert, InteractsWithQueue, Queueable, SerializesModels;

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
            throw new DataLostException('Folder Data Lost');
        }

        $transactions = [];
        $error = false;
        $index = 0;

        $this->folders->load([
            'series',
            'videos.metadata',
        ]);

        foreach ($this->folders as $folder) {
            try {
                $stored = [];
                $changes = [];

                $series = Series::firstOrCreate(['composite_id' => $folder->path], ['folder_id' => $folder->id]);

                $stored = $series->toArray();

                $changes['episodes'] = $folder->videos->count();

                $changes['primary_media_type'] = $folder->primary_media_type;

                if (is_null($series->title)) {
                    $changes['title'] = $folder->name;
                }

                $thumbnailPath = explode('/', $series->composite_id ?? 'unsorted/unsorted')[0] . '/' . basename($series->id);
                $thumbnailIsInternal = strpos($series->thumbnail_url, str_replace('http://', '', str_replace('https://', '', config('api.app_url'))));

                if (isset($series->thumbnail_url) && ! $thumbnailIsInternal && strlen(trim($series->thumbnail_url)) > 0) {
                    $thumbnailResult = $this->getThumbnailAsFile($series->thumbnail_url, $thumbnailPath);
                    if ($thumbnailResult) {
                        $changes['raw_thumbnail_url'] = $series->thumbnail_url;
                        $changes['thumbnail_url'] = $thumbnailResult;
                        dump('got thumbnail for ' . $series->id . ' at ' . $thumbnailResult . ' from ' . $changes['raw_thumbnail_url']);
                    }
                } elseif (isset($series->thumbnail_url) && $thumbnailIsInternal && ! Storage::disk('public')->exists("thumbnails/$thumbnailPath.webp")) {
                    Log::warning(
                        "Local thumbnail is set but does not exist for $series->composite_id at " . Storage::disk('public')->path("thumbnails/$thumbnailPath.webp"),
                        [
                            'path' => Str::after(urldecode($series->thumbnail_url), '/storage/'),
                            'exists' => Storage::disk('public')->exists(Str::after(urldecode($series->thumbnail_url), '/storage/')),
                        ]
                    );
                }

                $totalSize = $folder->total_size;
                if ($stored['total_size'] !== $totalSize) {
                    $changes['total_size'] = $totalSize;
                }

                if (! empty($changes)) {
                    array_push($transactions, [...$stored, ...$changes]);
                    /**
                     * DEBUG
                     *
                     * dump([...$stored, ...$changes]);
                     * dump($changes);
                     * dump($folder->name);
                     */
                }

                $index += 1;
                $this->taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index / count($this->folders)) * 100)]);
            } catch (\Throwable $th) {
                $error = true;

                $this->handleError('Error inserting verified folder series data', $th, [], count($transactions), count($this->folders));
            }
        }

        try {
            if (empty($transactions) || $error) {
                return 'No Changes Found';
            }

            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes', 'thumbnail_url', 'raw_thumbnail_url', 'total_size', 'primary_media_type']);

            $summary = 'Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']);
            dump($summary);

            return $summary;
        } catch (\Throwable $th) {
            $ids = array_column($transactions, 'id');

            $this->handleError('Error inserting verified folder series data', $th, $ids, count($transactions));
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
            Log::warning("Unable to download thumbnail image from $url for $compositePath: " . $th->getMessage());
        }

        return false;
    }
}

<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Exceptions\DataLostException;
use App\Models\Series;
use App\Models\SeriesSizeHistory;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifyFolders extends ManagedSubTask {
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
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->verifyFolders($taskService);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function verifyFolders(TaskService $taskService) {
        if (count($this->folders) == 0) {
            throw new DataLostException('Folder Data Lost');
        }

        $transactions = [];
        $sizeHistoryTransactions = [];

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
                $sizeHistoryChanges = [];

                $series = Series::firstOrCreate(['composite_id' => $folder->path], ['folder_id' => $folder->id]);

                $stored = $series->toArray();

                $relatedFileCount = $folder->videos->count();
                if (! isset($stored['episodes'])) { // Only set episode count initially, since it is a user editable field
                    $changes['episodes'] = $relatedFileCount;
                }

                if ($relatedFileCount !== $stored['file_count']) {
                    $changes['file_count'] = $relatedFileCount;
                    $sizeHistoryChanges['file_count'] = $relatedFileCount;
                }

                $primary_media_type = $folder->primary_media_type;
                if ($stored['primary_media_type'] !== $primary_media_type) {
                    $changes['primary_media_type'] = $folder->primary_media_type;
                }

                $totalSize = $folder->total_size;
                if ($stored['total_size'] !== $totalSize) {
                    $changes['total_size'] = $totalSize;
                    $sizeHistoryChanges['total_bytes'] = $totalSize;
                }

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
                        Log::info("Downloaded external thumbnail for {$series->id}.", [
                            'series' => $series->id,
                            'src' => $changes['raw_thumbnail_url'],
                            'dst' => $thumbnailResult,
                        ]);
                    }
                } elseif (isset($series->thumbnail_url) && $thumbnailIsInternal && ! Storage::disk('public')->exists("thumbnails/$thumbnailPath.webp")) {
                    // This means the thumbnail is set with another internal image url (for example cover art from a song was used as a thumbnail for some folder)
                    Log::warning(
                        "Local thumbnail is set but does not exist for $series->composite_id at " . Storage::disk('public')->path("thumbnails/$thumbnailPath.webp"),
                        [
                            'path' => Str::after(urldecode($series->thumbnail_url), '/storage/'),
                            'exists' => Storage::disk('public')->exists(Str::after(urldecode($series->thumbnail_url), '/storage/')),
                        ]
                    );
                }

                if (! empty($changes)) {
                    $changes['updated_at'] = now();
                    $transactions[] = [...$stored, ...$changes];
                    /**
                     * DEBUG
                     *
                     * dump([...$stored, ...$changes]);
                     * dump($changes);
                     * dump($folder->name);
                     */
                }
                if (! empty($sizeHistoryChanges)) {
                    $sizeHistoryTransactions[] = ['series_id' => $series->id, 'total_bytes' => $totalSize, 'file_count' => $relatedFileCount, 'recorded_at' => now()];
                }

                $index += 1;
                $taskService->updateSubTask($this->subTaskId, ['progress' => (int) (($index / count($this->folders)) * 100)]);
            } catch (\Throwable $th) {
                $error = true;

                $this->handleError('Error inserting verified folder series data', $th, [], count($transactions), count($this->folders));
            }
        }

        try {
            if ($error || empty($transactions)) { // size history transactions rely on there being folder changes because they are only created on changes
                return 'No Changes Found';
            }

            DB::beginTransaction();

            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes', 'thumbnail_url', 'raw_thumbnail_url', 'total_size', 'file_count', 'primary_media_type', 'updated_at']);

            if (! empty($sizeHistoryTransactions)) {
                SeriesSizeHistory::insert($sizeHistoryTransactions);
            }
            DB::commit();

            return 'Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']);
        } catch (\Throwable $th) {
            $ids = array_column($transactions, 'id');

            $this->handleError('Error inserting verified folder series data', $th, $ids, count($transactions));
        }
    }

    private function getThumbnailAsFile($url, $compositePath) {
        try {
            $response = Http::get($url);
            if ($response->successful()) {
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

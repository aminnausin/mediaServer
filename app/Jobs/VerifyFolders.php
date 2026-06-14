<?php

namespace App\Jobs;

use App\Enums\ImageType;
use App\Enums\TaskStatus;
use App\Exceptions\DataLostException;
use App\Models\Series;
use App\Models\SeriesSizeHistory;
use App\Models\SubTask;
use App\Models\Task;
use App\Services\Images\ImageService;
use App\Services\TaskService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifyFolders extends ManagedSubTask {
    /**
     * Create a new job instance.
     */
    public function __construct(public Collection $folders, int $taskId) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Verify ' . count($folders) . ' Folders']); //
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService, ImageService $imageService): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->verifyFolders($taskService, $imageService);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function verifyFolders(TaskService $taskService, ImageService $imageService) {
        if (count($this->folders) == 0) {
            throw new DataLostException('Folder Data Lost');
        }

        $transactions = [];
        $sizeHistoryTransactions = [];

        $error = false;
        $index = 0;
        $downloadedPosterCount = 0;

        $taskUserId = Task::find($this->taskId)->user_id;

        $this->folders->load([
            'series.primaryPoster',
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

                // region Poster

                /**
                 * Only checks for external posters, and downloads if not already downloaded
                 *
                 * But it should only check if it was updated because the url stays in the db row for user to see?
                 * otherwise its doing this poster check every time
                 *
                 * theres also if it was replaced with upload or something then it will always overwrite? idk
                 *
                 * Eventually can pull poster from 3rd party API
                 */
                $externalPosterDownloaded = $series->primaryPoster && $series->primaryPoster->source_url === $series->thumbnail_url;
                $posterIsExternal = filter_var($series->thumbnail_url, FILTER_VALIDATE_URL) && ! str_contains($series->thumbnail_url, config('app.host'));

                if ($posterIsExternal && ! $externalPosterDownloaded) {
                    $image = $imageService->downloadFromUrl($series->thumbnail_url, $series, ImageType::POSTER, $series->editor_id ?? $taskUserId);

                    if ($image) {
                        $changes['primary_poster_id'] = $image->id;
                        $changes['poster_updated_at'] = now();
                        $changes['thumbnail_url'] = asset("/storage/{$image->path}");
                        Log::info("Downloaded external thumbnail for {$series->composite_id}.", [
                            'series' => $series->id,
                            'src' => $series->thumbnail_url,
                            'dst' => $image->path,
                        ]);
                        $downloadedPosterCount++;
                    }
                }

                // endregion

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

            Series::upsert($transactions, 'id', ['folder_id', 'title', 'episodes', 'thumbnail_url', 'raw_thumbnail_url', 'total_size', 'file_count', 'primary_media_type', 'updated_at', 'primary_poster_id', 'poster_updated_at']);

            if (! empty($sizeHistoryTransactions)) {
                SeriesSizeHistory::insert($sizeHistoryTransactions);
            }
            DB::commit();

            $downloadedPosterSummary = $downloadedPosterCount > 0 ? " and downloaded {$downloadedPosterCount} posters" : '';

            return 'Updated ' . count($transactions) . ' folders from id ' . ($transactions[0]['folder_id']) . ' to ' . ($transactions[count($transactions) - 1]['folder_id']) . $downloadedPosterSummary;
        } catch (\Throwable $th) {
            $ids = array_column($transactions, 'id');

            $this->handleError('Error inserting verified folder series data', $th, $ids, count($transactions));
        }
    }
}

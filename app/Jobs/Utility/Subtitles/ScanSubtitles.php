<?php

namespace App\Jobs\Utility\Subtitles;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Jobs\VerifyFiles;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Models\Subtitle;
use App\Services\Subtitles\SubtitleManager;
use App\Services\Subtitles\SubtitleScanner;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ScanSubtitles extends ManagedSubTask {
    private string $uuid;

    private bool $externalOnly;

    public function __construct(int $taskId, string $uuid, bool $externalOnly = false) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Scan Subtitles for uuid ' . $uuid]);
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
        $this->uuid = $uuid;
        $this->externalOnly = $externalOnly;
    }

    public function handle(TaskService $taskService, SubtitleScanner $subtitleScanner, SubtitleManager $manager): void {
        if (! $this->beginSubTask($taskService)) {
            return;
        }

        try {
            $summary = $this->handleScanSubtitles($subtitleScanner, $manager);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    /**
     * This will handle clearing stale subtitles and indexing new or updated files
     *
     * Responsibilities
     *
     * Clear existing subtitle files (purge directory)
     * Clear related subtitle rows in db
     *
     * Grab extended metadata
     * Extract info for all embeded subtitle tracks and build transactions
     * Check file directory for related subtitles
     * build external subtitle transactions
     * bulk upsert embedded and external subtitle transactions
     *
     * Optional/Future Responsibilities
     *
     * bulk extract subtitle files from media file (based on user config)
     * bulk extract font files from media file (based on user config)
     * generate font information (as a json on metadata?)
     */
    private function handleScanSubtitles(SubtitleScanner $subtitleScanner, SubtitleManager $manager): string {
        $metadata = Metadata::with('video.folder')->where('uuid', $this->uuid)->firstOrFail();
        $media = $metadata->video;

        if (is_null($media)) {
            throw new ModelNotFoundException("Media file missing for uuid {$this->uuid}");
        }

        $manager->purgeSubtitles($metadata, $this->externalOnly);

        // absolute file path in storage
        $filePath = VerifyFiles::getAbsoluteMediaPath($media);
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $folderPath = dirname($filePath);
        $fileMetaData = VerifyFiles::getFileMetadata($filePath, 'Subtitle Scan');

        // Embedded Subtitles

        $subtitleTransactions = $this->externalOnly ? [] : $subtitleScanner->scanEmbeddedSubtitles($this->uuid, $fileMetaData);

        // External Subtitles

        // Previous Optimisation: if directory updated, check directory for related subtitle files and make subtitle transactions for them with stream 0
        // Now: every directory is scanned again per file. The scan is fast and should not have a high cost until you hit a large file count. Ideally this should be optimised again but without the batch structure it is difficult?
        // If it runs with horizon, it should be fast. In a synchronous queue it will probably be slow
        // Maybe I can batch this job by directory? or separate the external subtitle portion
        $externalSubtitles = $subtitleScanner->findExternalSubtitlesInDirectory($folderPath);
        $relevantExternal = array_filter(
            $externalSubtitles,
            fn($sub) => strtolower($sub['media_filename']) === strtolower($fileName)
        );

        $externalTransactions = $subtitleScanner->buildSubtitleTransactions($this->uuid, $relevantExternal);
        $subtitleTransactions = array_merge($subtitleTransactions, $externalTransactions);

        try {
            if (!empty($subtitleTransactions)) {
                Subtitle::upsert($subtitleTransactions, ['metadata_uuid', 'source_key'], ['language', 'title', 'codec', 'is_default', 'is_forced', 'external_path']);
            }

            $metadata->update(['subtitles_scanned_at' => now()]);

            return 'Found ' . count($subtitleTransactions) . ' subtitle track(s) for uuid ' . $this->uuid;
        } catch (\Throwable $th) {
            $keys = array_column($subtitleTransactions, 'source_key');
            $this->handleError('Error inserting subtitles for ' . $this->uuid, $th, $keys, count($subtitleTransactions));
        }
    }
}

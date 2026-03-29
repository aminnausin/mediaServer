<?php

namespace App\Jobs\Utility\Subtitles;

use App\Data\Subtitles\SubtitleScanTarget;
use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Jobs\VerifyFiles;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Models\Subtitle;
use App\Services\Subtitles\SubtitleManager;
use App\Services\Subtitles\SubtitleScanner;
use App\Services\TaskService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanSubtitles extends ManagedSubTask {
    private array $targets;

    private string $folderPath;

    private bool $scanExternal;

    /** @param SubtitleScanTarget[] $targets */
    public function __construct(int $taskId, array $targets, string $folderPath, bool $scanExternal) {
        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Scan ' . count($targets) . ' Files for Subtitles']);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;

        $this->targets = $targets;
        $this->folderPath = $folderPath;
        $this->scanExternal = $scanExternal;
    }

    public function handle(TaskService $taskService, SubtitleScanner $subtitleScanner, SubtitleManager $manager): void {
        if (! $this->beginSubTask($taskService, 'Scanning for subtitle tracks in folder ' . $this->folderPath)) {
            return;
        }

        try {
            $summary = 'Scanning for subtitle tracks in folder ' . $this->folderPath . "\n" . $this->handleScanSubtitles($subtitleScanner, $manager);
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
        $summary = '';
        $batchTransactions = [];
        $scannedUuids = [];

        $metadataMap = Metadata::with('video.folder')
            ->whereIn('uuid', array_map(fn ($t) => $t->uuid, $this->targets))
            ->get()
            ->keyBy('uuid');

        $externalSubtitles = $this->scanExternal
            ? $subtitleScanner->findExternalSubtitlesInDirectory($this->folderPath)
            : [];

        foreach ($this->targets as $target) {
            $subtitleTransactions = [];

            $uuid = $target->uuid;
            $fileUpdated = $target->fileUpdated;

            $metadata = $metadataMap[$uuid] ?? null;
            $media = $metadata?->video;

            if (is_null($metadata)) {
                Log::error('Metadata missing when scanning for subtitles', ['uuid' => $uuid]);

                continue;
            }

            if (is_null($media)) {
                Log::error('Media file missing on metadata instance when scanning for subtitles', ['uuid' => $metadata->uuid, 'title' => $metadata->title]);
                $summary .= "\n\nMedia file missing for " . $metadata->title . ' with uuid ' . $metadata->uuid;

                continue;
            }

            $manager->purgeSubtitles($metadata, externalOnly: ! $fileUpdated); // only purge external subtitle files if the file was not updated. Getting to this point

            // absolute file path in storage
            $filePath = VerifyFiles::getAbsoluteMediaPath($media);
            $fileName = pathinfo($filePath, PATHINFO_FILENAME);

            // Embedded Subtitles - scanned only if file was updated
            if ($fileUpdated) {
                // TODO: Cache this output from index or subsequent verify jobs as json in the model and never call from here
                $fileMetaData = VerifyFiles::getFileMetadata($filePath, 'Subtitle Scan');
                $subtitleTransactions = $subtitleScanner->scanEmbeddedSubtitles($uuid, $fileMetaData);
            }

            // External Subtitles - directory scanned once per job and filtered per media file
            if (! empty($externalSubtitles)) {
                $relevantExternal = array_filter(
                    $externalSubtitles,
                    fn ($sub) => strtolower($sub['media_filename']) === strtolower($fileName)
                );

                $subtitleTransactions = array_merge($subtitleTransactions, $subtitleScanner->buildSubtitleTransactions($uuid, $relevantExternal));
            }

            if (! empty($subtitleTransactions)) {
                $summary .= "\n\nFound " . count($subtitleTransactions) . ' subtitle track(s) for uuid ' . $uuid;
                $batchTransactions = array_merge($batchTransactions, $subtitleTransactions);
            }
            $scannedUuids[] = $uuid;
        }

        try {
            DB::transaction(function () use ($batchTransactions, $scannedUuids) {
                if (! empty($batchTransactions)) {
                    Subtitle::upsert($batchTransactions, ['metadata_uuid', 'source_key'], ['language', 'title', 'codec', 'is_default', 'is_forced', 'external_path']);
                }

                Metadata::whereIn('uuid', $scannedUuids)
                    ->update(['subtitles_scanned_at' => now()]);
            });

            return $summary . "\n\n\nTotal subtitle tracks upserted: " . count($batchTransactions);
        } catch (\Throwable $th) {
            $this->handleError('Error inserting subtitle tracks', $th, $scannedUuids, count($batchTransactions));
        }
    }
}

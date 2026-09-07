<?php

namespace App\Jobs\Metadata;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Jobs\VerifyFiles;
use App\Models\Font;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\Fonts\FontExtractionService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExtractFonts extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    public int $tries = 1;

    public int $backoff = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $filePath, string $uuid, int $taskId) {
        $this->filePath = $filePath;
        $this->uuid = $uuid;

        $subTask = SubTask::create([
            'task_id' => $taskId,
            'status' => TaskStatus::PENDING,
            'name' => 'Extract embedded fonts from ' . basename(dirname($filePath)) . '/' . basename($filePath),
            'reference_uuid' => $uuid,
            'reference_type' => Font::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService, FontExtractionService $extractor): void {
        if (! $this->beginSubTask($taskService, "Extracting fonts from $this->filePath")) {
            return;
        }

        try {
            $summary = $this->handleExtractFonts($extractor);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            Log::warning('Font extraction failed', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->updateFontsScannedAt(null);
            $this->failSubTask($taskService, $th);
            // Don't stop subsequent jobs if this one fails (so don't throw)
            if ($this->batch()->totalJobs === 1) {
                throw $th;
            } // Throw on reset fonts which is for one file
        }
    }

    private function handleExtractFonts(FontExtractionService $extractor): string {
        $metadata = Metadata::where('uuid', $this->uuid)->firstOrFail();
        $metadata->load('video');

        $mediaPath = VerifyFiles::getAbsoluteMediaPath($metadata->video);
        if (! file_exists($mediaPath)) {
            throw new FileNotFoundException("File not found: {$mediaPath}");
        }

        $metadata->raw_metadata = $this->verifyMetadata($metadata, $mediaPath);

        $start = microtime(true);

        $attachments = $extractor->parseFontAttachments($metadata);
        $extractedFonts = $extractor->extractFonts(
            mediaPath: $mediaPath,
            uuid: $metadata->uuid,
            attachments: $attachments,
        );

        $extractedFonts = $extractor->deduplicateFonts($extractedFonts);

        $timeElapsed = round(microtime(true) - $start, 2);

        DB::transaction(function () use ($metadata, $extractor, $extractedFonts) {
            $extractor->upsertFontData($extractedFonts);
            $extractor->clearDeletedFonts($metadata);
            $this->updateFontsScannedAt(now());
        });

        return 'Extracted ' . count($extractedFonts) . " embedded fonts for {$this->uuid} in {$timeElapsed}s";
    }

    /**
     * Updates storyboard scanned flag on metadata based on the uuid
     *
     * @param  ?Carbon  $time  Timestamp saved to metadata (null on error)
     */
    private function updateFontsScannedAt(?Carbon $time) {
        Metadata::where('uuid', $this->uuid)->update([
            'fonts_scanned_at' => $time,
        ]);
    }

    // Persist raw metadata
    private function verifyMetadata(Metadata $metadata, string $filePath): array {
        if (! empty($metadata->raw_metadata)) {
            return $metadata->raw_metadata;
        }

        Log::warning('raw_metadata missing for updated file in extract fonts', [
            'uuid' => $metadata->uuid,
            'title' => $metadata->title,
            'file_path' => $filePath,
        ]);

        $rawMetadata = VerifyFiles::getFileMetadata($filePath, 'Extract Fonts'); // fallback but don't update metadata due to separation of concerns?

        $metadata->update([
            'raw_metadata' => $rawMetadata,
        ]);

        return $rawMetadata;
    }
}

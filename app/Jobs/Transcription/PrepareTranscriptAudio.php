<?php

namespace App\Jobs\Transcription;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\Cache\TranscriptionCache;
use App\Services\FFmpeg\FFmpegService;
use App\Services\TaskService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;

class PrepareTranscriptAudio extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    public int $tries = 1;

    public int $backoff = 0;

    public int $timeout = 600;

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
            'name' => 'Prepare audio for transcription',
            'reference_uuid' => $uuid,
            'reference_type' => self::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService, FFmpegService $ffmpeg): void {
        if (! $this->beginSubTask($taskService, 'Extracting audio')) {
            return;
        }

        try {
            $summary = $this->handlePrepareAudio($ffmpeg);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            Log::warning('Failed to prepare transcript audio', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->failSubTask($taskService, $th);
            @unlink(TranscriptionCache::audio($this->taskId, $this->uuid));
            throw $th;
        }
    }

    private function handlePrepareAudio(FFmpegService $ffmpeg): string {
        $metadata = Metadata::where('uuid', $this->uuid)->firstOrFail();

        // Just to make sure it still exists in db
        if (! $metadata->video_id) {
            throw new FileNotFoundException("No video found for metadata: {$this->uuid}");
        }

        if (! file_exists($this->filePath)) {
            throw new FileNotFoundException("File not found: {$this->filePath}");
        }

        $outputPath = TranscriptionCache::audio($this->taskId, $this->uuid);
        $started = microtime(true);

        $ffmpeg->extractAudio($this->filePath, $outputPath);

        return sprintf('Extracted audio (%s MB, %ds)', round(filesize($outputPath) / 1048576, 1), round(microtime(true) - $started));
    }
}

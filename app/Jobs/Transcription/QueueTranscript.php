<?php

namespace App\Jobs\Transcription;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Jobs\VerifyFiles;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class QueueTranscript extends ManagedSubTask {
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
            'name' => 'Queue transcription of ' . basename(dirname($filePath)) . '/' . basename($filePath),
            'reference_uuid' => $uuid,
            'reference_type' => QueueTranscript::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        if (! $this->beginSubTask($taskService, "Queue transcription of $this->filePath")) {
            return;
        }

        try {
            $summary = $this->handleQueueTranscript();
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            Log::warning('Failed to queue transcription job', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->failSubTask($taskService, $th);
            // throw so ingestion does not run
            throw $th;
        }
    }

    private function handleQueueTranscript(): string {
        $metadata = Metadata::with('video')->where('uuid', $this->uuid)->firstOrFail();

        if (! $metadata->video) {
            throw new FileNotFoundException("No video found for metadata: {$this->uuid}");
        }

        $mediaPath = VerifyFiles::getAbsoluteMediaPath($metadata->video);
        if (! file_exists($mediaPath)) {
            throw new FileNotFoundException("File not found: {$mediaPath}");
        }

        $jobId = (string) Str::uuid();

        $downloadUrl = URL::temporarySignedRoute(
            'internal.transcription.download',
            now()->addHours(2),
            ['media' => $metadata->video_id],
        );

        Redis::connection('services')->rpush('queues:transcribe-service', json_encode([
            'job_id' => $jobId,
            'media_id' => $metadata->video_id,
            'metadata_id' => $metadata->id,
            'model' => 'large-v3',
            'download_url' => $downloadUrl,
            'task_id' => $this->taskId,
        ]));

        return 'Transcription generation queued';
    }
}

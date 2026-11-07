<?php

namespace App\Jobs\Transcription;

use App\Enums\TaskStatus;
use App\Exceptions\TranscriptionException;
use App\Jobs\ManagedSubTask;
use App\Models\SubTask;
use App\Services\Cache\TranscriptionCache;
use App\Services\TaskService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateTranscript extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    public int $tries = 1;

    public int $backoff = 0;

    public int $timeout = 1860;

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
            'name' => 'Transcribe ' . basename(dirname($filePath)) . '/' . basename($filePath),
            'reference_uuid' => $uuid,
            'reference_type' => self::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        if (! $this->beginSubTask($taskService, "Transcribing $this->filePath")) {
            return;
        }

        $audio = TranscriptionCache::audio($this->taskId, $this->uuid);

        try {
            $summary = $this->handleQueueTranscript($audio);
            $this->completeSubTask($taskService, $summary);
        } catch (\Throwable $th) {
            Log::warning('Failed to generate transcript', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->failSubTask($taskService, $th);
            throw $th;
        } finally {
            @unlink($audio);
        }
    }

    private function handleQueueTranscript(string $audio): string {
        if (! file_exists($audio)) {
            throw new FileNotFoundException("Prepared audio missing: {$audio}");
        }

        $started = microtime(true);

        $response = Http::timeout(1800)
            ->retry(3, 2000, fn ($e) => $e instanceof ConnectionException, throw: false)
            ->attach('file', fopen($audio, 'r'), basename($audio))
            ->post(config('services.transcribe.url'));

        if ($response->failed()) {
            throw new TranscriptionException('Transcription failed.', $response->status(), $response->json('detail') ?? $response->body());
        }

        if (! $response->json('vtt')) {
            throw new TranscriptionException('Transcription service returned an invalid response.', 502);
        }

        Storage::disk('local')->put(TranscriptionCache::resultKey($this->taskId, $this->uuid), $response->body());

        return sprintf(
            'Transcribed %d segments, with language %s in (%ds)',
            $response->json('segment_count'),
            $response->json('language'),
            round(microtime(true) - $started),
        );
    }
}

<?php

namespace App\Jobs\Transcription;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\Cache\TranscriptionCache;
use App\Services\TaskService;
use App\Services\TranscriptionService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SaveTranscript extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    public int $tries = 1;

    public int $backoff = 0;

    public function __construct(string $filePath, string $uuid, int $taskId) {
        $this->filePath = $filePath;
        $this->uuid = $uuid;

        $subTask = SubTask::create([
            'task_id' => $taskId,
            'status' => TaskStatus::PENDING,
            'name' => 'Save transcript',
            'reference_uuid' => $uuid,
            'reference_type' => self::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    public function handle(TaskService $taskService, TranscriptionService $transcripts): void {
        if (! $this->beginSubTask($taskService, "Saving transcript for {$this->filePath}")) {
            return;
        }

        try {
            $key = TranscriptionCache::resultKey($this->taskId, $this->uuid);

            if (! Storage::disk('local')->exists($key)) {
                throw new FileNotFoundException('Transcription Result not found');
            }
            $result = json_decode(Storage::disk('local')->get($key), true, flags: JSON_THROW_ON_ERROR);
            $metadata = Metadata::where('uuid', $this->uuid)->firstOrFail();
            $transcripts->saveTranscript($metadata, $result['vtt'], $result['language']);

            Storage::disk('local')->delete($key);
            $this->completeSubTask($taskService, "Saved transcript ({$result['language']})");
        } catch (\Throwable $th) {
            Log::warning('Failed to Save transcript', ['uuid' => $this->uuid, 'error' => $th->getMessage()]);

            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }
}

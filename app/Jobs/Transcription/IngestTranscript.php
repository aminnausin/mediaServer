<?php

namespace App\Jobs\Transcription;

use App\Enums\TaskStatus;
use App\Jobs\ManagedSubTask;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\TaskService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IngestTranscript extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    protected string $vtt;

    protected string $language;

    public int $tries = 1;

    public int $backoff = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $filePath, string $uuid, int $taskId, ?int $subTaskId = null, ?string $vtt = '', ?string $language = '') {
        $this->filePath = $filePath;
        $this->uuid = $uuid;
        $this->vtt = $vtt;
        $this->language = $language;
        $this->taskId = $taskId;

        if ($subTaskId !== null) {
            $this->subTaskId = $subTaskId;

            return;
        }

        $subTask = SubTask::create([
            'task_id' => $taskId,
            'status' => TaskStatus::PENDING,
            'name' => 'Save generated transcript of ' . basename(dirname($filePath)) . '/' . basename($filePath),
            'reference_uuid' => $uuid,
            'reference_type' => IngestTranscript::class,
        ]);

        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        $title = "generated transcript of $this->filePath" . ($this->language ? " in language {$this->language}" : '');
        if (! $this->beginSubTask($taskService, 'Save ' . $title)) {
            return;
        }

        try {
            $this->handleSaveTranscript();
            $this->completeSubTask($taskService, 'Saved ' . $title);
        } catch (\Throwable $th) {
            Log::warning('Font extraction failed', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->failSubTask($taskService, $th);
            throw $th;
        }
    }

    private function handleSaveTranscript(): string {
        $metadata = Metadata::with('video')->where('uuid', $this->uuid)->firstOrFail();

        $subtitle = $metadata->subtitles()->firstOrNew([
            'source_key' => 'generated',
            'track_id' => -1,
        ]);

        $oldPath = $subtitle->path;

        $subtitle->fill([
            'language' => $this->language,
            'title' => 'Auto-generated Transcript',
            'codec' => 'vtt',
            'format' => 'vtt',
            'is_default' => ! $metadata->subtitles()->exists(),
            'is_forced' => false,
            'source_key' => 'generated',
        ]);

        $path = $subtitle->getFilePath('vtt', $this->language);
        $subtitle->path = $path;
        $subtitle->save();

        Storage::disk('local')->put($path, $this->vtt);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return 'Saved transcript for ';
    }
}

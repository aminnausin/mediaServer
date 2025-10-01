<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Video;
use App\Services\TaskService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[DeleteWhenMissingModels]
class EmbedUidInMetadata implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    protected $uuid;

    protected $taskId;

    protected $subTaskId;

    protected $videoId;

    protected $startedAt;

    protected $taskService;

    public $timeout = 86400;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $uuid, $taskId, $videoId = null) {
        $this->filePath = $filePath;
        $this->uuid = $uuid;
        $this->videoId = $videoId;

        $this->taskService = App::make(TaskService::class);
        $subTask = $this->taskService->createSubTask(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Embed UID in video file ' . basename(dirname($filePath)) . '/' . basename($filePath)]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService): void {
        $this->taskService = $taskService;

        if ($this->batch()?->cancelled() || (($task = Task::find($this->taskId)) && $task->status == TaskStatus::CANCELLED)) {
            // Determine if the batch or parent task has been cancelled...
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::CANCELLED, 'summary' => 'Parent Task was Cancelled']);

            return;
        }

        $this->startedAt = now();
        $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_pending' => '--']);
        $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::PROCESSING, 'started_at' => $this->startedAt, 'summary' => "Adding uuid to $this->filePath"]);

        try {
            $summary = $this->handleEmbed();
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);

            $task = $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_complete' => '++'], false);
            $this->taskService->updateSubTask($this->subTaskId, [
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'progress' => 100,
                'ended_at' => $endedAt,
                'duration' => $duration,
            ]);

            if ($this->videoId) {
                Video::where('id', $this->videoId)->update(['uuid' => $this->uuid]);
            }

            if ($this->batch()) {
                return;
            }

            // Update the parent task if this was dispatched and not batched

            // Task not found or there are still pending tasks
            if (! $task || $task->sub_tasks_pending != 0) {
                return;
            }
            $status = $task->sub_tasks_complete < $task->sub_tasks_total ? TaskStatus::INCOMPLETE : TaskStatus::COMPLETED;
            $ended_at = now();

            $started_at = $task->started_at ? \Carbon\Carbon::parse($task->started_at) : null;
            $duration = $started_at ? (int) $ended_at->diffInSeconds($started_at) : 0;

            $this->taskService->updateTask($task->id, [
                'status' => $status,
                'ended_at' => $ended_at,
                'duration' => $duration < 0 ? $duration * -1 : $duration,
            ], $status === TaskStatus::COMPLETED);
        } catch (\Throwable $th) {
            $endedAt = now();
            $duration = (int) $this->startedAt->diffInSeconds($endedAt);
            dump($th->getMessage());
            $this->taskService->updateTaskCounts($this->taskId, ['sub_tasks_failed' => '++']);
            $this->taskService->updateSubTask($this->subTaskId, ['status' => TaskStatus::FAILED, 'summary' => 'Error: ' . $th->getMessage(), 'ended_at' => $endedAt, 'duration' => $duration]);
            if ($this->batch()) {
                throw $th;
            }
            $this->taskService->updateTask($this->taskId, ['status' => TaskStatus::FAILED], true);
            Log::error('Task Failed', ['task_id' => $this->taskId, 'subtask_id' => $this->subTaskId, 'error' => $th->getMessage()]);
        }
    }

    private function handleEmbed() {
        $ext = pathinfo($this->filePath, PATHINFO_EXTENSION);

        dump("Adding uuid $this->uuid to $this->filePath");

        if (! file_exists($this->filePath)) {
            dump('UUID Fail file does not exist');
            throw new \Exception('UUID Fail file does not exist');
        }

        if (! uuid_is_valid($this->uuid)) {
            dump('Invalid UUID');
            throw new \Exception('Invalid UUID');
        }

        $tempFilePath = $this->filePath . '.tmp';

        $formatMap = ['mp4' => 'mp4', 'mkv' => 'matroska', 'mp3' => 'mp3', 'ogg' => 'opus', 'flac' => 'flac'];
        $format = $formatMap[$ext] ?? $ext;

        if ($ext === 'mp4') {
            try {
                $this->addMetadataWithExifTool();

                return ' ExifTool';
            } catch (\Exception $e) {
                dump('ExifTool failed, falling back to ffmpeg: ' . $e->getMessage());
            }
        }

        $this->addMetadataWithFFMpeg($format, $tempFilePath);

        if (file_exists($tempFilePath)) {
            dump("Cleaning up temporary file: $tempFilePath");
            unlink($tempFilePath);  // delete the temp file
        }

        return ' FFmpeg';
    }

    private function addMetadataWithExifTool() {
        dump('Attempting to add uuid using exiftool');

        $command = [
            'exiftool',
            '-encoder=' . $this->uuid, // ExifTool sucks and can't write a custom tag into files like ffmpeg so I have to use encoder
            '-preserve',
            '-overwrite_original',
            $this->filePath,
        ];

        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception('ExifTool failed: ' . $process->getErrorOutput());
        }

        // nothing to do
        if (strpos($process->getOutput(), 'Nothing to do') !== false) {
            throw new \Exception('ExifTool: Nothing to do, no changes made to the file.');
        }

        dump('ExifTool succeeded in adding uuid');
    }

    private function addMetadataWithFFMpeg($format, $tempFilePath) {
        dump('Fallback to ffmpeg to add uuid');

        $command = [
            'ffmpeg',
            '-i',
            $this->filePath,
            '-c',
            'copy',
            '-movflags',
            'use_metadata_tags',
            '-metadata',
            "uuid=$this->uuid",
            '-f',
            $format,
            $tempFilePath,
        ];

        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (file_exists($tempFilePath)) {
            // Get the original file's timestamps
            $originalModifiedTime = filemtime($this->filePath);
            // Replace the original file with the temporary file
            rename($tempFilePath, $this->filePath);
            // Restore the original timestamps
            touch($this->filePath, $originalModifiedTime);
        } else {
            throw new \Exception('Failed to create the temporary file with metadata.');
        }
    }
}

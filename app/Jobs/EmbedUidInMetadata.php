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
        $subTask = $this->taskService->createSubTask(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Embed UID in video file ' . basename($filePath)]);

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
            if ($task->sub_tasks_complete < $task->sub_tasks_total) {
                $this->taskService->updateTask($this->taskId, ['status' => TaskStatus::INCOMPLETE], true);

                return;
            }

            $this->taskService->updateTask($this->taskId, ['status' => TaskStatus::COMPLETED], true);
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
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (file_exists($tempFilePath)) {
            // Get the original file's timestamps
            // $originalCreatedTime = filectime($this->filePath);
            $originalModifiedTime = filemtime($this->filePath);
            // Replace the original file with the temporary file
            rename($tempFilePath, $this->filePath);
            // Restore the original timestamps
            // touch($this->filePath, $originalModifiedTime, $originalCreatedTime);
            touch($this->filePath, $originalModifiedTime);
        } else {
            throw new \Exception('Failed to create the temporary file with metadata.');
        }
    }

    private function getUidFromMetadata($filePath) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        $command = [
            'ffprobe',
            '-v',
            'quiet',
            '-print_format',
            'json',
            '-show_format',
            '-show_streams',
            $filePath,
        ];

        // Execute the FFmpeg command
        $process = new Process($command);
        $process->run();

        // Check if the process was successful
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput(); // Decode JSON output
        $metadata = json_decode($output, true);
        if ($ext === 'ogg') {
            $metadata['format'] = $metadata['streams'][0] ?? [];
        }
        dump($metadata);

        // Tag was previously uid
        $uid = isset($metadata['format']['tags']['uid']) ? $metadata['format']['tags']['uid'] : (isset($metadata['format']['tags']['UID']) ? $metadata['format']['tags']['UID'] : null);

        return isset($metadata['format']['tags']['uuid']) ? $metadata['format']['tags']['uuid'] : $uid;
    }
}

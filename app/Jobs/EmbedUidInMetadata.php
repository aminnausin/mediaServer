<?php

namespace App\Jobs;

use App\Enums\TaskStatus;
use App\Models\SubTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[DeleteWhenMissingModels]
class EmbedUidInMetadata implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $uid;

    protected $taskId;
    protected $subTaskId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $uid, $taskId) {
        $this->filePath = $filePath;
        $this->uid = $uid;

        $subTask = SubTask::create(['task_id' => $taskId, 'status' => TaskStatus::PENDING, 'name' => 'Embed UID in video file']); //
        DB::table('tasks')->where('id', $this->taskId)->update([
            'sub_tasks_pending' => DB::raw('sub_tasks_pending + 1'),
            'sub_tasks_total' => DB::raw('sub_tasks_total + 1'),
        ]);
        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        DB::table('tasks')->where('id', $this->taskId)->decrement('sub_tasks_pending');
        SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::PROCESSING, 'started_at' => now(), 'summary' => "Adding uuid to $this->filePath"]);

        try {
            $summary = $this->handleEmbed();
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_complete');
            SubTask::where('id', $this->subTaskId)->update([
                'status' => TaskStatus::COMPLETED,
                'summary' => $summary,
                'ended_at' => now(),
                'progress' => 100,
            ]);
        } catch (\Throwable $th) {
            DB::table('tasks')->where('id', $this->taskId)->increment('sub_tasks_failed');
            SubTask::where('id', $this->subTaskId)->update(['status' => TaskStatus::FAILED, 'summary' => "Error: " . $th->getMessage(), 'ended_at' => now()]);
        }
    }

    private function handleEmbed() {
        $ext = pathinfo($this->filePath, PATHINFO_EXTENSION);

        dump("Adding uuid to $this->filePath");

        if (! file_exists($this->filePath)) {
            dump('UUID Fail file does not exist');
            throw new \Exception('UUID Fail file does not exist');
        }

        $tempFilePath = $this->filePath . '.tmp';

        $formatMap = ['mp4' => 'mp4', 'mkv' => 'matroska', 'mp3' => 'mp3'];
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
            "uid=$this->uid",
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
        $command = [
            'ffprobe',
            '-v',
            'quiet',
            '-print_format',
            'json',
            '-show_format',
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
        dump($metadata);

        return isset($metadata['format']['tags']['uid']) ? $metadata['format']['tags']['uid'] : (isset($metadata['format']['tags']['UID']) ? $metadata['format']['tags']['UID'] : null);
    }
}

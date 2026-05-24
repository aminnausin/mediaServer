<?php

namespace App\Jobs\Metadata;

use App\Enums\TaskStatus;
use App\Exceptions\StoryboardNotSupportedException;
use App\Jobs\ManagedSubTask;
use App\Models\Metadata;
use App\Models\Storyboard;
use App\Models\SubTask;
use App\Services\Ffmpeg\FFmpegCommandBuilder;
use App\Services\Images\Storyboard\StoryboardOptions;
use App\Services\TaskService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateStoryboard extends ManagedSubTask {
    protected string $filePath;

    protected string $uuid;

    public int $timeout = 600;

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
            'name' => 'Generate storyboard for ' . basename(dirname($filePath)) . '/' . basename($filePath),
            'reference_uuid' => $uuid,
            'reference_type' => Storyboard::class,
        ]);

        $this->taskId = $taskId;
        $this->subTaskId = $subTask->id;
    }

    /**
     * Execute the job.
     */
    public function handle(TaskService $taskService, FFmpegCommandBuilder $builder): void {
        if (! $this->beginSubTask($taskService, "Generating storyboard for $this->filePath")) {
            return;
        }

        try {
            $summary = $this->handleGenerateStoryboard($builder);
            $this->completeSubTask($taskService, $summary);
        } catch (StoryboardNotSupportedException $th) {
            $this->skipSubTask($taskService, 'Skipped: ' . $th->getMessage());
            $this->updateStoryboardScannedAt(now());
        } catch (\Throwable $th) {
            Log::warning('Storyboard generation failed', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            $this->updateStoryboardScannedAt(null);
            $this->failSubTask($taskService, $th);
            // Don't stop subsequent jobs if this one fails (so don't throw)
            if ($this->batch()->totalJobs === 1) {
                throw $th;
            } // Throw on reset storyboard which is for one file
        }
    }

    private function handleGenerateStoryboard(FFmpegCommandBuilder $builder): string {
        $outputDir = 'metadata/' . substr($this->uuid, 0, 2) . "/{$this->uuid}/storyboard";

        $publicDisk = Storage::disk('public');
        $publicDisk->makeDirectory($outputDir);

        $metadata = Metadata::where('uuid', $this->uuid)->firstOrFail();
        $metadata->load('video');

        $filePath = $publicDisk->path(str_replace('storage/', '', $metadata->video->path));
        $options = StoryboardOptions::fromMetadata($metadata, $filePath);
        $tile_count = ceil($metadata->duration * $options->fps);

        $command = $builder->storyboard(
            filePath: $filePath,
            outputPattern: $publicDisk->path($outputDir) . '/%d.jpg',
            options: $options,
        );

        $start = microtime(true);

        $process = new Process($command);
        $process->setTimeout(600);
        $command = str_replace('"^%"', '%', str_replace('\\', '/', $process->getCommandLine()));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $th) {
            Log::error('Storyboard command failed', [
                'uuid' => $this->uuid,
                'file' => basename($this->filePath),
                'command' => $command,
                'error' => $process->getErrorOutput(),
                'th' => $th->getMessage(),
            ]);
            throw new \RuntimeException('FFmpeg failed: ' . $process->getErrorOutput());
        }

        $timeElapsed = round(microtime(true) - $start, 2);

        $sheets = $publicDisk->files($outputDir);
        $sheetCount = count($sheets);

        if ($sheetCount === 0) {
            throw new \RuntimeException('FFmpeg produced no output files');
        }

        DB::transaction(function () use ($options, $tile_count, $timeElapsed, $command) {
            Storyboard::updateOrCreate(['metadata_uuid' => $this->uuid], [
                'tile_rows' => $options->rows,
                'tile_cols' => $options->cols,
                'tile_width' => $options->width,
                'tile_height' => $options->height,
                'tile_count' => $tile_count,
                'interval_seconds' => 1 / $options->fps,
                'modified_at' => now(),
                'generation_time' => $timeElapsed,
                'command' => $command,
            ]);

            $this->updateStoryboardScannedAt(now());
        });

        return "Generated storyboard for {$this->uuid} in {$timeElapsed}s";
    }

    /**
     * Updates storyboard scanned flag on metadata based on the uuid
     *
     * @param  ?Carbon  $time  Timestamp saved to metadata (null on error)
     */
    private function updateStoryboardScannedAt(?Carbon $time) {
        Metadata::where('uuid', $this->uuid)->update([
            'storyboard_scanned_at' => $time,
        ]);
    }
}

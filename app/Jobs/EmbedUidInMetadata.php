<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[DeleteWhenMissingModels]
class EmbedUidInMetadata implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    protected $uid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filePath, $uid) {
        $this->filePath = $filePath;
        $this->uid = $uid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $ext = pathinfo($this->filePath, PATHINFO_EXTENSION);

        dump("Adding uuid to $this->filePath");

        if (! file_exists($this->filePath)) {
            dump('UUID Fail file does not exist');

            return;
        }

        $tempFilePath = $this->filePath . '.tmp';

        $formatMap = ['mp4' => 'mp4', 'mkv' => 'matroska'];
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

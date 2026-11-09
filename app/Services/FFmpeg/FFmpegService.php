<?php

namespace App\Services\FFmpeg;

use App\Exceptions\FFmpegException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

readonly class FFmpegLog {
    public function __construct(
        public float $duration,
        public string $command,
    ) {}
}

class FFmpegService {
    public function __construct(private FFmpegCommandBuilder $commands) {}

    public function extractAudio(string $input, string $output, ?ExtractAudioOptions $options = null, int $timeout = 540): void {
        File::ensureDirectoryExists(dirname($output));
        $partial = $output . '.part';

        try {
            $this->run($this->commands->extractAudio($input, $partial, $options ?? new ExtractAudioOptions), $timeout);
            rename($partial, $output);
        } finally {
            @unlink($partial);
        }
    }

    private function run(array $command, int $timeout): FFmpegLog {
        $process = new Process($command);
        $process->setTimeout($timeout);

        $rawCommand = str_replace('"^%"d.jpg', '%d.jpg', $process->getCommandLine());
        $start = microtime(true);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            Log::error('FFmpeg command failed', [
                'command' => $rawCommand,
                'error' => $process->getErrorOutput(),
                'th' => $e->getMessage(),
            ]);

            throw new FFmpegException('FFmpeg failed: ' . $process->getErrorOutput());
        }

        return new FFmpegLog(round(microtime(true) - $start, 2), $rawCommand);
    }
}

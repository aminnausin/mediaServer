<?php

namespace App\Services\Subtitles\Formats;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VttStrategy implements SubtitleFormatStrategy {
    public function convert(string $inputPath, string $outputPath): void {
        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            Storage::disk('local')->path($inputPath),
            '-c:s',
            'webvtt',
            Storage::disk('local')->path($outputPath),
        ]);

        $process->mustRun();
    }
}

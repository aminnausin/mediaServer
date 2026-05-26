<?php

namespace App\Services\Ffmpeg;

use Symfony\Component\Process\Process;

class FFprobeService {
    /**
     * Returns the number of detected keyframes in a video
     *
     * Uses -skip_frame nokey
     *
     * @param  string  $filePath  Path to video
     */
    public static function countKeyframes(string $filePath): int {
        $process = new Process([
            'ffprobe',
            '-select_streams',
            'v',
            '-skip_frame',
            'nokey',
            '-show_frames',
            '-show_entries',
            'frame=pict_type',
            '-of',
            'csv=p=0',
            $filePath,
        ]);

        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            return 0;
        }

        return substr_count($process->getOutput(), 'I');
    }
}

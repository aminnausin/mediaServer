<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SubtitleExtractor {
    public function extractStream(Metadata $metadata, Subtitle $subtitle) {
        try {
            $mediaPath = $metadata->video->path;
            $ext = $this->getExtentionFromCodec($subtitle['codec']);

            $outputPath = $this->getOutputPath($subtitle, $ext);

            $command = [
                'ffmpeg',
                '-y',
                '-i',
                $mediaPath, // Media is on public disk for now does not need storage disk, path already has storage in it?
                '-map',
                "0:$subtitle->track_id",
                Storage::disk('local')->path($outputPath),
            ];

            $process = new Process($command);
            $process->mustRun();

            $subtitle->update([
                'path' => $outputPath,
                'format' => $ext,
            ]);

            return $outputPath;
        } catch (ProcessFailedException $th) {
            Log::error('Subtitle extraction failed', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'command' => $th->getProcess()->getCommandLine(),
                'exit_code' => $th->getProcess()->getExitCode(),
                'error' => $th->getProcess()->getErrorOutput(),
            ]);
            throw $th;
        }
    }

    /**
     * Given a Subtitle row and file extention, ensure the relative output directory exists and get the relative output path.
     */
    private function getOutputPath(Subtitle $subtitle, string $ext) {
        Storage::disk('local')->makeDirectory($subtitle->getDirectoryPath()); // Ensure directory exists

        return $subtitle->getFilePath($ext);
    }

    private function getExtentionFromCodec(string $codec): string {
        return match ($codec) {
            'subrip' => 'srt',
            'ass' => 'ass',
            'ssa' => 'ssa',
            'webvtt' => 'vtt',
            default => 'bin',
        };
    }
}

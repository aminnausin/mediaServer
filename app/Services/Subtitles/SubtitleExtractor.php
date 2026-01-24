<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SubtitleExtractor {
    /**
     * Extracts a specified subtitle track from a media file and ensures the output exists after extraction.
     *
     * @param  Metadata  $metadata  Information about the media file
     * @param  Subtitle  $subtitle  Information about the subtitle track
     */
    public function extractStream(Metadata $metadata, Subtitle $subtitle) {
        try {
            $mediaPath = $metadata->video->path;
            $ext = $this->getExtensionFromCodec($subtitle['codec']);

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

            if (! Storage::disk('local')->exists($outputPath)) {
                throw new \RuntimeException("Output file $outputPath does not exist after extraction");
            }

            $subtitle->update([
                'path' => $outputPath,
                'format' => $ext,
            ]);

            return $outputPath;
        } catch (ProcessFailedException $e) {
            Log::error('Subtitle extraction failed (process)', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'command' => $e->getProcess()->getCommandLine(),
                'exit_code' => $e->getProcess()->getExitCode(),
                'error' => $e->getProcess()->getErrorOutput(),
            ]);
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Subtitle extraction failed (general)', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'error' => $th->getMessage(),
            ]);
            throw $th;
        }
    }

    /**
     * Given a Subtitle row and file extension, ensure the relative output directory exists and get the relative output path.
     */
    private function getOutputPath(Subtitle $subtitle, string $ext) {
        Storage::disk('local')->makeDirectory($subtitle->getDirectoryPath()); // Ensure directory exists

        return $subtitle->getFilePath($ext);
    }

    private function getExtensionFromCodec(string $codec): string {
        return match ($codec) {
            'subrip' => 'srt',
            'ass' => 'ass',
            'ssa' => 'ssa',
            'webvtt' => 'vtt',
            default => 'bin',
        };
    }
}

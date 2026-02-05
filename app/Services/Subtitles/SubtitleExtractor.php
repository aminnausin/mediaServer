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
        $extractionStart = microtime(true);
        $timings = [];

        try {
            $mediaPath = $metadata->video->path;
            $ext = $this->getExtensionFromCodec($subtitle['codec']);

            $pathStart = microtime(true);
            $outputPath = $this->getOutputPath($subtitle, $ext);
            $timings['get_output_path'] = microtime(true) - $pathStart;

            $commandStart = microtime(true);
            $command = [
                'ffmpeg',
                '-y', // overwrite existing
                '-i',
                "file:$mediaPath", // Media is on public disk for now does not need storage disk, path already has storage in it?
                '-copyts',
                '-map',
                "0:$subtitle->track_id", // read the specific stream id
                '-vn', // skip reading video
                '-an', // skip reading audio
                '-c:s',
                'copy', // don't re-encode
                Storage::disk('local')->path($outputPath),
            ];
            $timings['build_command'] = microtime(true) - $commandStart;

            $ffmpegStart = microtime(true);
            $process = new Process($command);
            $process->mustRun();
            $timings['ffmpeg_execution'] = microtime(true) - $ffmpegStart;

            $verifyStart = microtime(true);
            if (! Storage::disk('local')->exists($outputPath)) {
                throw new \RuntimeException("Output file $outputPath does not exist after extraction");
            }
            $timings['verify_output'] = microtime(true) - $verifyStart;

            $updateStart = microtime(true);
            $subtitle->update([
                'path' => $outputPath,
                'format' => $ext,
            ]);
            $timings['db_update'] = microtime(true) - $updateStart;

            $timings['total_extraction'] = microtime(true) - $extractionStart;

            Log::info('Subtitle extraction completed', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'codec' => $subtitle->codec,
                'output_format' => $ext,
                'media_path' => $mediaPath,
                'output_path' => $outputPath,
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);

            return $outputPath;
        } catch (ProcessFailedException $e) {
            $timings['total_extraction'] = microtime(true) - $extractionStart;
            Log::error('Subtitle extraction failed (process)', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'command' => $e->getProcess()->getCommandLine(),
                'exit_code' => $e->getProcess()->getExitCode(),
                'error' => $e->getProcess()->getErrorOutput(),
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);
            throw $e;
        } catch (\Throwable $th) {
            $timings['total_extraction'] = microtime(true) - $extractionStart;
            Log::error('Subtitle extraction failed (general)', [
                'subtitle_id' => $subtitle->id,
                'track_id' => $subtitle->track_id,
                'metadata_uuid' => $subtitle->metadata_uuid,
                'error' => $th->getMessage(),
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
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

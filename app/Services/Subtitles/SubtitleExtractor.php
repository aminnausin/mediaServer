<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
                Storage::disk('public')->path($mediaPath), // Media is on public disk for now
                '-map',
                "0:$subtitle->track_id",
                Storage::disk('local')->path($outputPath),
            ];

            $process = new Process($command);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new \Exception('Subtitles Failed: "' . implode(' ', $command) . '"');
            }

            $subtitle->update([
                'path' => $outputPath,
                'format' => $ext,
            ]);

            return $outputPath;
        } catch (\Throwable $th) {
            Log::error('Unable to get file subtitles', ['error' => $th->getMessage()]);
            throw $th;
        }
    }

    /**
     * Get the relative output path given the subtitle and file extention
     */
    private function getOutputPath(Subtitle $subtitle, string $ext) {
        $relativeOutputDir = "data/media/{$subtitle->metadata_uuid}/subtitles";
        Storage::disk('local')->makeDirectory($relativeOutputDir); // Ensure directory exists

        return "{$relativeOutputDir}/{$subtitle->track_id}.{$ext}"; // get final output address
    }

    private function getExtentionFromCodec(string $codec): string {
        return match ($codec) {
            'subrip' => 'srt',
            'ass' => 'ass',
            'ssa' => 'ssa',
            'webvtt' => 'vtt',
            'hdmv_pgs_subtitle' => 'sup',
            'dvd_subtitle' => 'sub',
            default => 'bin',
        };
    }
}

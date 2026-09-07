<?php

namespace App\Services\Fonts;

use App\Data\Fonts\ExtractedFontData;
use App\Data\Fonts\FontAttachmentData;
use App\Exceptions\FFmpegException;
use App\Models\Font;
use App\Models\Metadata;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FontExtractionService {
    /**
     * Extracts fronts from from a media file and ensures the output exists after extraction.
     *
     * @param  string  $mediaPath  absolute path to media file
     * @param  FontAttachmentData[]  $attachments
     * @return ExtractedFontData[]
     */
    public function extractFonts(string $uuid, string $mediaPath, array $attachments): array {
        $extractionStart = microtime(true);
        $timings = [];
        $extractedFonts = [];

        try {
            $shard = substr($uuid, 0, 2);

            $disk = Storage::disk('public');
            $relativeDir = "metadata/metadata/{$shard}/{$uuid}/fonts/";
            $disk->makeDirectory($relativeDir);

            if (! $disk->exists($relativeDir)) {
                throw new RuntimeException("Failed to create font directory: {$relativeDir}");
            }

            $absoluteDir = str_replace('\\', '/', $disk->path($relativeDir));

            foreach ($attachments as $attachment) {
                $relativePath = "{$relativeDir}{$attachment->fileName}";
                $outputPath = "{$absoluteDir}{$attachment->fileName}";

                $commandStart = microtime(true);
                $command = [
                    'ffmpeg',
                    "-dump_attachment:{$attachment->streamIndex}",
                    $outputPath,
                    '-i',
                    "file:{$mediaPath}",
                    '-t',
                    '0',
                    '-f',
                    'null',
                    'null',
                ];

                $timings['build_command'] = microtime(true) - $commandStart;

                $ffmpegStart = microtime(true);
                $process = new Process($command);
                $process->mustRun();
                $timings['ffmpeg_execution'] = microtime(true) - $ffmpegStart;

                $verifyStart = microtime(true);
                if (! $disk->exists($relativePath)) {
                    throw new \RuntimeException("Font attachment was not extracted: {$attachment->fileName}");
                }
                $timings['verify_output'] = microtime(true) - $verifyStart;

                $extractedFonts[] = new ExtractedFontData(
                    metadataUuid: $uuid,
                    fileName: $attachment->fileName,
                    codec: $attachment->codec ? strtolower($attachment->codec) : null,
                    mimeType: $attachment->mimeType,
                    size: filesize($outputPath),
                    path: $relativePath,
                    hash: hash_file('sha256', $outputPath),
                );
            }

            $timings['total_extraction'] = microtime(true) - $extractionStart;

            return $extractedFonts;
        } catch (ProcessFailedException $e) {
            $timings['total_extraction'] = microtime(true) - $extractionStart;
            Log::error('Font extraction failed (process)', [
                'metadata_uuid' => $uuid,
                'command' => $e->getProcess()->getCommandLine(),
                'exit_code' => $e->getProcess()->getExitCode(),
                'error' => $e->getProcess()->getErrorOutput(),
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);
            throw new FFmpegException('FFmpeg failed: ' . $e->getProcess()->getErrorOutput());
        } catch (\Throwable $th) {
            $timings['total_extraction'] = microtime(true) - $extractionStart;
            Log::error('Font extraction failed (general)', [
                'metadata_uuid' => $uuid,
                'error' => $th->getMessage(),
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);
            throw $th;
        }
    }

    /**
     * Parse the embedded font attachments from metadata.
     *
     * @return FontAttachmentData[]
     */
    public function parseFontAttachments(Metadata $metadata): array {
        if ($metadata->raw_metadata === null) {
            return [];
        }
        $rawMetadata = $metadata->raw_metadata;

        $attachments = collect($rawMetadata['streams'])->filter(fn (array $stream) => ($stream['codec_type'] ?? null) === 'attachment');

        $fontAttachments = $attachments->map(function (array $stream): FontAttachmentData {
            $filename = $stream['tags']['filename'] ?? throw new RuntimeException('Font attachment is missing a filename.');
            $mimeType = $stream['tags']['mimetype'] ?? 'application/octet-stream';

            return new FontAttachmentData(
                streamIndex: $stream['index'],
                fileName: $filename,
                codec: $stream['codec_name'] ?? null,
                mimeType: $mimeType,
            );
        });

        return $fontAttachments->values()->all();
    }

    /**
     * Parse the embedded font attachments from metadata.
     *
     * @param  ExtractedFontData[]  $extractedFonts
     */
    public function upsertFontData(array $extractedFonts): void {
        Font::upsert(
            array_map(
                fn (ExtractedFontData $font): array => [
                    'metadata_uuid' => $font->metadataUuid,
                    'file_name' => $font->fileName,
                    'codec' => $font->codec,
                    'mime_type' => $font->mimeType,
                    'size' => $font->size,
                    'path' => $font->path,
                    'hash' => $font->hash,
                    'updated_at' => now(),
                ],
                $extractedFonts,
            ),
            ['metadata_uuid', 'file_name'],
            [
                'codec',
                'mime_type',
                'path',
                'size',
                'hash',
                'updated_at',
            ]
        );
    }

    /**
     * @param  ExtractedFontData[]  $extractedFonts
     * @return ExtractedFontData[]
     */
    public function deduplicateFonts(array $extractedFonts): array {
        return collect($extractedFonts)->groupBy(fn (ExtractedFontData $font) => $font->fileName)->map(fn ($fonts) => $fonts->sortByDesc(
            fn (ExtractedFontData $font) => $font->size
        )->first())->values()->all();
    }

    public function clearDeletedFonts(Metadata $metadata): void {
        $metadata->fonts()->whereNull('path')->delete();
    }
}

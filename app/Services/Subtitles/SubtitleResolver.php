<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubtitleResolver {
    public function __construct(protected SubtitleExtractor $extractor, protected SubtitleFormatter $formatter) {}

    public function resolveSubtitles(Metadata $metadata, int $track, string $format, ?string $language = null): BinaryFileResponse {
        $startTime = microtime(true);
        $timings = [];

        try {
            $queryStart = microtime(true);
            if ($track === 0 && $language) {
                // find external track by language
                $subtitle = $metadata->subtitles()
                    ->where('track_id', 0)
                    ->where('language', $language)
                    ->firstOrFail();
            } else {
                $subtitle = $metadata->subtitles()
                    ->where('track_id', $track)
                    ->firstOrFail();
            }
            $timings['db_query'] = microtime(true) - $queryStart;

            $format = ltrim(strtolower($format), '.');

            $pathStart = microtime(true);
            $requestedPath = $subtitle->getFilePath($format, $language);
            $timings['get_file_path'] = microtime(true) - $pathStart;

            $existsStart = microtime(true);
            $fileExists = $this->fileExists($requestedPath);

            $timings['file_exists_check'] = microtime(true) - $existsStart;

            if ($fileExists) {
                $timings['total'] = microtime(true) - $startTime;
                $timings['cache_hit'] = true;

                Log::info('Subtitle resolved (cached)', [
                    'metadata_uuid' => $metadata->uuid,
                    'track' => $track,
                    'format' => $format,
                    'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
                ]);

                return Response::file(Storage::disk('local')->path($requestedPath));
            }

            $timings['cache_hit'] = false;

            // if ($this->fileExists($requestedPath)) {
            //     return Response::file(Storage::disk('local')->path($requestedPath));
            // }

            if (! $metadata->video_id) {
                abort(404);
            }

            if ($subtitle->track_id === 0 && $subtitle->external_path && ! $subtitle->path) {
                $externalStart = microtime(true);
                $this->resolveExternalSubtitle($subtitle);
                $timings['external_subtitle_copy'] = microtime(true) - $externalStart;
            }

            $extractionCheckStart = microtime(true);
            $needsExtraction = ! $subtitle->path || ! $this->fileExists($subtitle->path);
            $timings['extraction_check'] = microtime(true) - $extractionCheckStart;

            if ($needsExtraction) {
                $extractionStart = microtime(true);
                $this->extractor->extractStream($metadata, $subtitle);
                $timings['extraction'] = microtime(true) - $extractionStart;
            }
            $refreshStart = microtime(true);
            $subtitle->refresh();
            $timings['subtitle_refresh'] = microtime(true) - $refreshStart;

            $conversionStart = microtime(true);
            $convertedPath = $this->formatter->convert($subtitle->path, $requestedPath, $format);
            $timings['format_conversion'] = microtime(true) - $conversionStart;

            $timings['total'] = microtime(true) - $startTime;

            Log::info('Subtitle resolved (generated)', [
                'metadata_uuid' => $metadata->uuid,
                'track' => $track,
                'format' => $format,
                'language' => $language,
                'needed_extraction' => $needsExtraction,
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);

            return Response::file($convertedPath);

            // return Response::file($this->formatter->convert($subtitle->path, $requestedPath, $format));
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $th) {
            // Log::error('Subtitle Resolver Failed', ['error' => $th->getMessage()]);
            $timings['total'] = microtime(true) - $startTime;
            Log::error('Subtitle Resolver Failed', [
                'error' => $th->getMessage(),
                'metadata_uuid' => $metadata->uuid ?? null,
                'track' => $track,
                'timings_ms' => array_map(fn ($t) => round($t * 1000, 2), $timings),
            ]);
            throw $th;
        }
    }

    /**
     * Copies an external subtitle file to the internal folder following the track.language.ext pattern
     */
    private function resolveExternalSubtitle(Subtitle $subtitle): void {
        $sourcePath = $subtitle->external_path;
        if (! $sourcePath) {
            throw new InvalidArgumentException('Subtitle has no external source path');
        }
        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $outputPath = $subtitle->getFilePath($ext, $subtitle->language);

        Storage::disk('local')->put($outputPath, Storage::disk('public')->get("$sourcePath"));

        $subtitle->update([
            'path' => $outputPath,
            'format' => $ext,
        ]);
    }

    private function fileExists(string $path): bool {
        return Storage::disk('local')->exists($path);
    }
}

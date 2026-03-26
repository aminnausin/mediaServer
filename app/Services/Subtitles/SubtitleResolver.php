<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use App\Models\Subtitle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Resolves API Requests
class SubtitleResolver {
    public function __construct(protected SubtitleExtractor $extractor, protected SubtitleFormatter $formatter) {}

    /**
     * Resolves subtitles from web request
     *
     * 1. By matching model in database
     * 2. Then by existing file on disk
     * 3. Then by extracting from video to disk
     * 4. Then by format
     */
    public function resolveSubtitles(Metadata $metadata, int $track, string $format, ?string $language = null): BinaryFileResponse {
        try {
            $format = ltrim(strtolower($format), '.');
            $subtitle = $this->findSubtitle($metadata, $track, $language);
            $requestedPath = $subtitle->getFilePath($format, $language);

            if ($this->fileExists($requestedPath)) {
                return Response::file(Storage::disk('local')->path($requestedPath));
            }

            // If file does not exist already and videoId is missing, abort early
            if (! $metadata->video_id) {
                abort(404);
            }

            if ($subtitle->track_id === 0 && $subtitle->external_path && ! $subtitle->path) {
                $this->resolveExternalSubtitle($subtitle);
            }

            // If subtitle was never extracted or the extracted file is missing, extract again
            $needsExtraction = ! $subtitle->path || ! $this->fileExists($subtitle->path);
            if ($needsExtraction) {
                $this->extractor->extractStream($metadata, $subtitle);
            }

            // Generate the correct file format based on the request (returns the generated file if it already matches)
            $subtitle->refresh();
            $convertedPath = $this->formatter->convert($subtitle->path, $requestedPath, $format);

            Log::info('Subtitle resolved (generated)', [
                'metadata_uuid' => $metadata->uuid,
                'track' => $track,
                'format' => $format,
                'language' => $language,
                'needed_extraction' => $needsExtraction,
            ]);

            return Response::file($convertedPath);
        } catch (\Throwable $th) {
            Log::error('Subtitle Resolver Failed', [
                'error' => $th->getMessage(),
                'metadata_uuid' => $metadata->uuid ?? null,
                'track' => $track,
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

    private function findSubtitle(Metadata $metadata, int $track, ?string $language): Subtitle {
        if ($track === 0 && $language) {
            // find external track by language
            return $metadata->subtitles()
                ->where('track_id', 0)
                ->where('language', $language)
                ->firstOrFail();
        }

        return $metadata->subtitles()
            ->where('track_id', $track)
            ->firstOrFail();
    }

    private function fileExists(string $path): bool {
        return Storage::disk('local')->exists($path);
    }
}

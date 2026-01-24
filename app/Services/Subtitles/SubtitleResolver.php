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
        try {
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

            $format = ltrim(strtolower($format), '.');

            $requestedPath = $subtitle->getFilePath($format, $language);
            if ($this->fileExists($requestedPath)) {
                return Response::file(Storage::disk('local')->path($requestedPath));
            }

            if (! $metadata->video_id) {
                abort(404);
            }

            if ($subtitle->track_id === 0 && $subtitle->external_path && ! $subtitle->path) {
                $this->resolveExternalSubtitle($subtitle);
            }

            if (! $subtitle->path || ! $this->fileExists($subtitle->path)) {
                $this->extractor->extractStream($metadata, $subtitle);
            }
            $subtitle->refresh();

            return Response::file($this->formatter->convert($subtitle->path, $requestedPath, $format));
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Subtitle Resolver Failed', ['error' => $th->getMessage()]);
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

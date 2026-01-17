<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubtitleResolver {
    public function __construct(protected SubtitleExtractor $extractor, protected SubtitleFormatter $formatter) {}

    public function resolveSubtitles(Metadata $metadata, int $track, string $format): BinaryFileResponse {
        try {
            $subtitle = $metadata->subtitles()->where('track_id', $track)->firstOrFail(); // 404 on no result

            $format = ltrim(strtolower($format), '.');
            $requestedPath = $subtitle->getFilePath($format);

            if ($this->fileExists($requestedPath)) {
                return Response::file(
                    Storage::disk('local')->path($requestedPath)
                );
            }

            if (! $metadata->video()) {
                abort(404);
            }

            if (! $subtitle->path || ! $this->fileExists($subtitle->path)) {
                $this->extractor->extractStream($metadata, $subtitle);
                $subtitle->refresh();
            }

            $nativePath = $subtitle->path;
            $convertedPath = $this->formatter->convert(
                $nativePath,
                $requestedPath,
                $format
            );

            if (! $this->fileExists($convertedPath)) {
                abort(500, 'Subtitle file does not exist after conversion.');
            }

            return Response::file(
                Storage::disk('local')->path($convertedPath)
            );
        } catch (ModelNotFoundException $th) {
            throw $th;
        } catch (\Throwable $th) {
            Log::error('Subtitle Resolver Failed', ['error' => $th->getMessage()]);
            throw $th;
        }
    }

    private function fileExists(string $path): bool {
        return Storage::disk('local')->exists($path);
    }
}

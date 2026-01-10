<?php

namespace App\Services\Subtitles;

use App\Models\Metadata;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubtitleResolver {
    public function __construct(protected SubtitleExtractor $extractor, protected SubtitleFormatter $formatter) {}

    public function resolveSubtitles(Metadata $metadata, int $track, string $format): BinaryFileResponse {
        $subtitle = $metadata->subtitles()->where('track_id', $track)->firstOrFail(); // 404 on no result

        $format = ltrim(strtolower($format), '.');
        $requestedPath = "data/media/{$subtitle->metadata_uuid}/subtitles/{$track}.{$format}";

        if ($this->fileExists($requestedPath)) {
            return Response::file(
                Storage::disk('local')->path($requestedPath)
            );
        }

        if (! $subtitle->path || ! $this->fileExists($subtitle->path)) {
            $this->extractor->extractStream($metadata, $subtitle); // Native format subtitle file (usually srt)
            $subtitle->refresh();
        }

        $nativePath = $subtitle->path;
        $convertedPath = $this->formatter->convert(
            $nativePath,
            $requestedPath,
            $format
        );

        if (! $this->fileExists($convertedPath)) {
            abort(500);
        }

        return Response::file(
            Storage::disk('local')->path($convertedPath)
        );
    }

    private function fileExists(string $path): bool {
        return Storage::disk('local')->exists($path);
    }
}

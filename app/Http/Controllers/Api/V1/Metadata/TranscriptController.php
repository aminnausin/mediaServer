<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Data\Access\RateLimitData;
use App\Exceptions\TranscriptionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubtitleResource;
use App\Models\Metadata;
use App\Models\Subtitle;
use App\Services\RateLimitService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TranscriptController extends Controller {
    public function __construct(protected RateLimitService $rateLimiter) {}

    public function regenerate(Metadata $metadata): JsonResponse {
        $limits = $this->getRateLimits();
        $isAdmin = Gate::allows('admin');

        if (! $isAdmin) {
            $rateLimitError = $this->rateLimiter->getRateLimitError($limits);

            if ($rateLimitError) {
                return $rateLimitError;
            }

            $this->rateLimiter->hitRateLimits($limits);
        }

        try {
            $transcript = $this->generateTranscript($metadata);
            $subtitle = $this->saveTranscript($metadata, $transcript['vtt'], $transcript['language']);

            return response()->json([
                'message' => 'Transcript generated successfully.',
                'subtitle' => new SubtitleResource($subtitle),
                'language' => $transcript['language'],
                'language_probability' => $transcript['language_probability'],
                'segment_count' => $transcript['segment_count'],
            ], 201);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (TranscriptionException $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->error()], $e->status());
        }
    }

    private function getRateLimits(): array {
        $userId = Auth::id();

        return [
            new RateLimitData(
                key: "transcript-regenerate:minute:{$userId}",
                maxAttempts: 1,
                decaySeconds: 60,
                message: 'Too many requests.',
            ),
            new RateLimitData(
                key: "transcript-regenerate:hour:{$userId}",
                maxAttempts: 10,
                decaySeconds: 3600,
                message: 'Hourly limit reached.',
            ),
        ];
    }

    private function generateTranscript(Metadata $metadata): array {
        $absolutePath = $this->getVideoPath($metadata);
        $response = Http::timeout(120)->attach('file', fopen($absolutePath, 'r'), basename($absolutePath))->post(config('services.transcribe.url'));

        if ($response->failed()) {
            throw new TranscriptionException('Transcription failed.', $response->status(), $response->json('detail') ?? $response->body());
        }

        if (! $response->json('vtt')) {
            throw new TranscriptionException('Transcription service returned an invalid response.', 502);
        }

        return $response->json();
    }

    private function getVideoPath(Metadata $metadata): string {
        $media = $metadata->video;
        $videoPath = str_starts_with($media->path, 'storage/')
            ? substr($media->path, 8)
            : $media->path;

        if (! $videoPath || ! Storage::disk('public')->exists($videoPath)) {
            throw new FileNotFoundException('File not found for ' . $metadata->composite_id);
        }

        return Storage::disk('public')->path($videoPath);
    }

    private function saveTranscript(Metadata $metadata, string $vtt, ?string $language): Subtitle {
        $subtitle = $metadata->subtitles()->firstOrNew([
            'source_key' => 'generated',
            'track_id' => -1,
        ]);

        $oldPath = $subtitle->path;

        $subtitle->fill([
            'language' => $language,
            'title' => 'Auto-generated Transcript',
            'codec' => 'vtt',
            'format' => 'vtt',
            'is_default' => ! $metadata->subtitles()->exists(),
            'is_forced' => false,
            'source_key' => 'generated',
        ]);

        $path = $subtitle->getFilePath('vtt', $language);
        $subtitle->path = $path;
        $subtitle->save();

        Storage::disk('local')->put($path, $vtt);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return $subtitle;
    }
}

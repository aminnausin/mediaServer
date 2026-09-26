<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Data\Access\RateLimitData;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubtitleResource;
use App\Jobs\VerifyFiles;
use App\Models\Metadata;
use App\Services\RateLimitService;
use Illuminate\Http\Client\ConnectionException;
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

        $rateLimitError = ! $isAdmin ? $this->rateLimiter->getRateLimitError($limits) : null;
        if ($rateLimitError) {
            return $rateLimitError;
        }

        if (! $isAdmin) {
            $this->rateLimiter->hitRateLimits($limits);
        }

        $videoPath = VerifyFiles::getAbsoluteMediaPath($metadata->video);
        $media = $metadata->video;
        $videoPath = str_starts_with($media->path, 'storage/')
            ? substr($media->path, 8)
            : $media->path;

        if (! $videoPath || ! Storage::disk('public')->exists($videoPath)) {
            return response()->json(['message' => Storage::disk('public')->path(''), 'p' => $videoPath], 404);
        }

        $absolutePath = Storage::disk('public')->path($videoPath);

        try {
            $response = Http::timeout(120)->attach('file', fopen($absolutePath, 'r'), basename($absolutePath))->post(config('services.transcribe.url'));
        } catch (ConnectionException $e) {
            return response()->json([
                'message' => 'Transcription service is unavailable. It sleeps after a period of inactivity, so it may need a moment to spin up. Please try again shortly.',
            ], 503);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'Transcription failed.',
                'error' => $response->json('detail') ?? $response->body(),
            ], $response->status());
        }

        $vtt = $response->json('vtt');
        $language = $response->json('language');

        if (! $vtt) {
            return response()->json(['message' => 'Transcription service returned an invalid response.'], 502);
        }

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
        ]);

        $subtitle->source_key = 'generated';

        $path = $subtitle->getFilePath('vtt', $language);
        $subtitle->path = $path;
        $subtitle->save();

        Storage::disk('local')->put($path, $vtt);

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return response()->json([
            'message' => 'Transcript generated successfully.',
            'subtitle' => new SubtitleResource($subtitle),
            'language' => $language,
            'language_probability' => $response->json('language_probability'),
            'segment_count' => $response->json('segment_count'),
        ]);
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
}

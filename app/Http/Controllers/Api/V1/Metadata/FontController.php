<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Metadata\FontResource;
use App\Models\Font;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\FileJobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class FontController extends Controller {
    public function __construct(protected FileJobService $fileJobService) {}

    /**
     * List storyboard details for metadata.
     */
    public function show(Metadata $metadata) {
        return response()->json([
            'fonts' => FontResource::collection($metadata->fonts)->resolve(),
            'fonts_scanned_at' => $metadata->fonts_scanned_at,
        ]);
    }

    public function regenerate(Metadata $metadata) {
        $isAdmin = Gate::allows('admin');

        $rateLimitError = ! $isAdmin ? $this->getRateLimitError() : null;
        if ($rateLimitError) {
            return $rateLimitError;
        }

        if (! $isAdmin) {
            $this->hitRateLimits();
        }

        $alreadyRunning = SubTask::where('reference_uuid', $metadata->uuid)
            ->where('reference_type', Font::class)
            ->whereIn('status', [TaskStatus::PENDING, TaskStatus::PROCESSING])
            ->latest()
            ->first();

        if ($alreadyRunning) {
            return response()->json(['message' => 'Font extraction already in progress', 'task_id' => $alreadyRunning->task_id], 409);
        }

        $FontDir = 'metadata/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/fonts";
        $metadata->fonts()->update([
            'path' => null,
        ]);
        Storage::disk('public')->deleteDirectory($FontDir);

        $task = $this->fileJobService->regenerateFonts(Auth::id(), $metadata);

        return response()->json(['task_id' => $task->id], 202);
    }

    private function getRateLimitError(): ?JsonResponse {
        $perMinuteKey = 'font-regenerate:minute:' . Auth::id();
        $perHourKey = 'font-regenerate:hour:' . Auth::id();

        if (RateLimiter::tooManyAttempts($perMinuteKey, 1)) {
            return response()->json([
                'message' => 'Too many requests. Try again in ' . RateLimiter::availableIn($perMinuteKey) . ' seconds.',
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($perHourKey, 15)) {
            return response()->json([
                'message' => 'Hourly limit reached. Try again in ' . ceil(RateLimiter::availableIn($perHourKey) / 60) . ' minutes.',
            ], 429);
        }

        return null;
    }

    private function hitRateLimits(): void {
        $perMinuteKey = 'font-regenerate:minute:' . Auth::id();
        $perHourKey = 'font-regenerate:hour:' . Auth::id();

        RateLimiter::hit($perMinuteKey, 60);
        RateLimiter::hit($perHourKey, 3600);
    }
}

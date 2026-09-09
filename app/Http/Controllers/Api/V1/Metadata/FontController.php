<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Data\Access\RateLimitData;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Metadata\FontResource;
use App\Models\Font;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\FileJobService;
use App\Services\RateLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FontController extends Controller {
    public function __construct(protected FileJobService $fileJobService, protected RateLimitService $rateLimiter) {}

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
        $limits = $this->getRateLimits();

        $isAdmin = Gate::allows('admin');

        $rateLimitError = ! $isAdmin ? $this->rateLimiter->getRateLimitError($limits) : null;
        if ($rateLimitError) {
            return $rateLimitError;
        }

        if (! $isAdmin) {
            $this->rateLimiter->hitRateLimits($limits);
        }

        $alreadyRunning = SubTask::where('reference_uuid', $metadata->uuid)
            ->where('reference_type', Font::class)
            ->whereIn('status', [TaskStatus::PENDING, TaskStatus::PROCESSING])
            ->latest()
            ->first();

        if ($alreadyRunning) {
            return response()->json(['message' => 'Font extraction already in progress', 'task_id' => $alreadyRunning->task_id], 409);
        }

        $fontDir = 'metadata/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/fonts";
        $metadata->fonts()->update([
            'path' => null,
        ]);
        Storage::disk('public')->deleteDirectory($fontDir);

        $task = $this->fileJobService->regenerateFonts(Auth::id(), $metadata);

        return response()->json(['task_id' => $task->id], 202);
    }

    private function getRateLimits(): array {
        $userId = Auth::id();

        return [
            new RateLimitData(
                key: "font-regenerate:minute:{$userId}",
                maxAttempts: 1,
                decaySeconds: 60,
                message: 'Too many requests.',
            ),
            new RateLimitData(
                key: "font-regenerate:hour:{$userId}",
                maxAttempts: 15,
                decaySeconds: 3600,
                message: 'Hourly limit reached.',
            ),
        ];
    }
}

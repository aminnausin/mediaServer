<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Metadata\FontResource;
use App\Models\Font;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Services\FileJobService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FontController extends Controller {
    public function __construct(protected FileJobService $fileJobService) {}

    /**
     * List storyboard details for metadata.
     */
    public function show(Metadata $metadata) {
        return FontResource::collection($metadata->fonts);
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
}

<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Metadata\StoryboardResource;
use App\Models\Metadata;
use App\Models\Storyboard;
use App\Models\SubTask;
use App\Services\FileJobService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryboardController extends Controller {
    public function __construct(protected FileJobService $fileJobService) {}

    /**
     * List storyboard details for metadata.
     */
    public function show(Metadata $metadata) {
        return new StoryboardResource($metadata->storyboard()->firstOrFail());
    }

    public function regenerate(Metadata $metadata) {
        $this->authorize('admin');

        $alreadyRunning = SubTask::where('reference_uuid', $metadata->uuid)
            ->where('reference_type', Storyboard::class)
            ->whereIn('status', [TaskStatus::PENDING, TaskStatus::PROCESSING])
            ->latest()
            ->first();

        if ($alreadyRunning) {
            return response()->json(['message' => 'Storyboard generation already in progress', 'task_id' => $alreadyRunning->task_id], 409);
        }

        $storyboardDir = 'metadata/' . substr($metadata->uuid, 0, 2) . "/{$metadata->uuid}/storyboard";
        $metadata->storyboard?->delete();
        Storage::disk('public')->deleteDirectory($storyboardDir);

        $task = $this->fileJobService->regenerateStoryboard(Auth::id(), $metadata);

        return response()->json(['task_id' => $task->id], 202);
    }
}

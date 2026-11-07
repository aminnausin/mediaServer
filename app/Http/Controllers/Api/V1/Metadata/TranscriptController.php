<?php

namespace App\Http\Controllers\Api\V1\Metadata;

use App\Data\Access\RateLimitData;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubtitleResource;
use App\Jobs\Transcription\GenerateTranscript;
use App\Jobs\Transcription\IngestTranscript;
use App\Jobs\Transcription\PrepareTranscriptAudio;
use App\Jobs\Transcription\QueueTranscript;
use App\Jobs\Transcription\SaveTranscript;
use App\Models\Metadata;
use App\Models\SubTask;
use App\Models\Subtitle;
use App\Services\FileJobService;
use App\Services\RateLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TranscriptController extends Controller {
    public function __construct(protected FileJobService $fileJobService, protected RateLimitService $rateLimiter) {}

    public function get(Request $request, Metadata $metadata) {
        $language = $request->query('language');
        $subtitle = Subtitle::where('metadata_uuid', $metadata->uuid)->when($language, fn ($query) => $query->where('language', $language))->firstOrFail();

        return response()->json(new SubtitleResource($subtitle));
    }

    public function generate(Metadata $metadata): JsonResponse {
        $alreadyRunning = SubTask::where('reference_uuid', $metadata->uuid)
            ->whereIn('reference_type', [
                QueueTranscript::class,
                IngestTranscript::class,
                PrepareTranscriptAudio::class,
                GenerateTranscript::class,
                SaveTranscript::class,
            ])
            ->whereIn('status', [TaskStatus::PENDING, TaskStatus::PROCESSING])
            ->whereHas('task', function ($query) {
                $query->whereIn('status', [
                    TaskStatus::PENDING,
                    TaskStatus::PROCESSING,
                ]);
            })
            ->latest()
            ->first();

        if ($alreadyRunning) {
            return response()->json(['message' => 'Transcript generation already in progress', 'task_id' => $alreadyRunning->task_id], 409);
        }

        $limits = $this->getRateLimits();
        $isAdmin = Gate::allows('admin');

        if (! $isAdmin) {
            $rateLimitError = $this->rateLimiter->getRateLimitError($limits);

            if ($rateLimitError) {
                return $rateLimitError;
            }

            $this->rateLimiter->hitRateLimits($limits);
        }

        $task = $this->fileJobService->generateTranscript(Auth::id(), $metadata);

        return response()->json(['task_id' => $task->id], 202);
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

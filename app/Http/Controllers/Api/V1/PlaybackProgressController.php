<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackProgressStoreRequest;
use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Services\Auth\GuestIdentity;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlaybackProgressController extends Controller {
    use HttpResponses;

    public function show(Metadata $metadata) {
        if (! Auth::check() && ! GuestIdentity::guestToken()) {
            return $this->forbidden();
        }

        $progress = GuestIdentity::scope(PlaybackProgress::query())
            ->where('metadata_id', $metadata->id)
            ->first(['progress_offset', 'progress_percentage']);

        return response()->json(['progress_offset' => $progress?->progress_offset ?? 0, 'progress_percentage' => $progress?->progress_percentage ?? 0]);
    }

    /**
     * Upserts a playback progress tracker associated with a user and a specific metadata
     */
    public function upsert(PlaybackProgressStoreRequest $request, Metadata $metadata): Response|JsonResponse {
        try {
            $validated = $request->validated();
            $identity = GuestIdentity::identity(); // user_id or guest_token

            $duration = $metadata->duration ?? 0;
            $progressOffset = min($validated['progress_offset'], $duration);
            $progressPct = $duration ? round($progressOffset * 100 / $duration) : 0;
            $threshold = config('playback.completion_percentage_threshold', 95);

            if ($progressPct >= $threshold) {
                $progress = PlaybackProgress::firstOrNew([...$identity, 'metadata_id' => $metadata->id]);

                if ($progress->progress_percentage < $threshold) {
                    $progress->last_completed_at = now();
                    $progress->completion_count++;
                }

                $progress->progress_offset = $duration;
                $progress->progress_percentage = 100;
                $progress->record_id = $validated['record_id'] ?? $progress->record_id;

                $progress->save();

                return response()->json(
                    $progress->only(['progress_offset', 'progress_percentage', 'completion_count'])
                );
            }

            $progress = GuestIdentity::upsert(
                'playback_progress',
                [
                    ...$identity,
                    'metadata_id' => $metadata->id,
                    'progress_offset' => $progressOffset,
                    'progress_percentage' => $progressPct,
                    'record_id' => $validated['record_id'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['progress_offset', 'progress_percentage', 'record_id', 'updated_at']
            );

            return response()->json(collect((array) $progress)->only(['progress_offset', 'progress_percentage', 'completion_count']));
        } catch (\Throwable $th) {
            Log::error('Playback progress store error', ['metadata_id' => $metadata?->id, 'msg' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return $this->error(null, 'Unable to store playback progress entry. Error: ' . $th->getMessage(), 500);
        }
    }
}

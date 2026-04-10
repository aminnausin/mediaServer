<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackProgressStoreRequest;
use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlaybackProgressController extends Controller {
    use HttpResponses;

    public function show(Metadata $metadata) {
        $progress = PlaybackProgress::where('user_id', Auth::id())->where('metadata_id', $metadata->id)->value('progress_offset');

        return response()->json(['progress_offset' => $progress ?? 0]);
    }

    /**
     * Upserts a playback progress tracker associated with a user and a specific metadata
     */
    public function upsert(PlaybackProgressStoreRequest $request, Metadata $metadata): Response|JsonResponse {
        try {
            $user_id = Auth::id();
            $validated = $request->validated();

            DB::transaction(function () use ($user_id, $validated, $metadata) {
                $progress = PlaybackProgress::firstOrNew([
                    'user_id' => $user_id,
                    'metadata_id' => $metadata->id,
                ]);

                $duration = $metadata->duration ?? 0;
                $progress_offset = min($validated['progress_offset'], $duration);
                $progress_pct = $duration ? round($progress_offset * 100 / $duration) : 0;
                $threshold = config('playback.completion_percentage_threshold', 95);

                $is_newly_completed = ($progress_pct >= $threshold && $progress->progress_percentage < $threshold);
                $is_completed = $progress_pct >= $threshold;

                $progress->progress_offset = $is_completed ? $metadata->duration : $progress_offset;
                $progress->progress_percentage = $is_completed ? 100 : $progress_pct;

                if ($is_newly_completed) {
                    $progress->last_completed_at = now();
                    $progress->completion_count++;
                }

                if (isset($validated['record_id'])) {
                    $progress->record_id = $validated['record_id'];
                }
                $progress->save();
            });

            return response()->noContent();
        } catch (\Throwable $th) {
            Log::error('Playback progress store error', ['metadata_id' => $metadata?->id, 'msg' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            return $this->error(null, 'Unable to store playback progress entry. Error: ' . $th->getMessage(), 500);
        }
    }
}

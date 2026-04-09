<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackProgressStoreRequest;
use App\Models\Metadata;
use App\Models\PlaybackProgress;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class PlaybackProgressController extends Controller {
    use HttpResponses;

    public function show(Metadata $metadata) {
        $progress = PlaybackProgress::where('user_id', Auth::id())->where('metadata_id', $metadata->id)->value('progress_offset');

        return response()->json(['progress_offset' => $progress ?? 0]);
    }

    /**
     * Upserts a playback progress tracker associated with a user and a specific metadata
     */
    public function upsert(PlaybackProgressStoreRequest $request, Metadata $metadata) {
        try {
            $user_id = Auth::id();
            $validated = $request->validated();

            $duration = $metadata->duration ?? 0;
            $progress_offset = min($validated['progress_offset'], $duration);

            $progress_pct = $duration ? round($progress_offset * 100 / $duration) : 0;

            if ($progress_pct >= config('playback.completion_percentage_threshold')) {
                $progress_offset = $metadata->duration;
                $progress_pct = 100;
            }

            PlaybackProgress::upsert(
                [
                    'user_id' => $user_id,
                    'metadata_id' => $metadata->id,
                    'progress_offset' => $progress_offset,
                    'progress_percentage' => $progress_pct,
                    'record_id' => $validated['record_id'] ?? null,
                    'updated_at' => now(),
                ],
                ['user_id', 'metadata_id'],
                ['progress_offset', 'progress_percentage', 'record_id', 'updated_at']
            );

            return response()->noContent();
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to store playback progress entry. Error: ' . $th->getMessage(), 500);
        }
    }
}

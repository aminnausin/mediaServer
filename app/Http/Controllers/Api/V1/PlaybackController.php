<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackStoreRequest;
use App\Models\Metadata;
use App\Models\Playback;
use App\Traits\HttpResponses;

class PlaybackController extends Controller {
    use HttpResponses;

    public function __construct() {
        $this->middleware('throttle:200,1')->only(['store']);
    }

    /**
     * Display a listing of the resource.
     * Get all playback data points from metadata ID for a video
     * Ordered By Progress 0 -> 100
     */
    public function show(int $id) {
        try {
            return Playback::where('metadata_id', $id)->oldest('progress')->get();
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get playback data. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaybackStoreRequest $request) {
        try {
            $validated = $request->validated();
            $playbackUpdates = 0;
            $metadata = null;

            foreach ($validated['entries'] as $entry) {
                if (is_null($metadata) || $metadata->id != $entry['metadata_id']) {
                    $metadata = Metadata::where('id', $entry['metadata_id'])->first();
                    if (! $metadata || ! $metadata->video()) {
                        return $this->error(null, 'Video metadata does not exist', 404);
                    }
                }

                $existing = Playback::where('metadata_id', $entry['metadata_id'])->where('progress', $entry['progress'])->first();

                if ($existing) {
                    $entry['count'] = $existing->count + 1;
                    $existing->update($entry);

                    // $playbackIndex = array_search($existing['id'], array_column($playback, 'id'));
                    // $playbackIndex !== false ? $playback[$playbackIndex] = $existing : $playback[] = $existing;
                } else {
                    $result = Playback::create($entry);
                }
                // $playback[] = $result;
                $playbackUpdates += 1;
            }

            return $this->success($playbackUpdates);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create playback record. Error: ' . $th->getMessage(), 500);
        }
    }
}

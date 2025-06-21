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
        $validated = $request->validated();
        $entries = $validated['entries'] ?? [];

        try {
            $lastMetadataId = null;
            $updates = 0;

            foreach ($entries as $entry) {
                if ($lastMetadataId !== $entry['metadata_id']) {
                    Metadata::findOrFail($entry['metadata_id']);
                    $lastMetadataId = $entry['metadata_id'];
                }

                $existing = Playback::query()
                    ->where('metadata_id', $entry['metadata_id'])
                    ->where('progress', $entry['progress'])
                    ->first();

                if ($existing) {
                    $entry['count'] = $existing->count + 1;
                    $existing->update($entry);
                } else {
                    Playback::create($entry);
                }

                $updates++;
            }

            return response()->json($updates);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create playback record. Error: ' . $th->getMessage(), 500);
        }
    }
}

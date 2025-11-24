<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackStoreRequest;
use App\Models\Metadata;
use App\Models\Playback;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Cache;

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
        return Cache::remember("playback_graph_{$id}", 120, function () use ($id) {
            return Playback::where('metadata_id', $id)->oldest('progress')->get();
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaybackStoreRequest $request) {
        $entries = $request->validated()['entries'] ?? [];

        try {
            $updates = 0;
            $lastMetadataId = null;

            foreach ($entries as $entry) {
                $metadataId = $entry['metadata_id'];

                if ($lastMetadataId !== $metadataId) {
                    Metadata::findOrFail($metadataId);
                    Cache::forget("playback_graph_{$metadataId}");
                    $lastMetadataId = $metadataId;
                }

                $existing = Playback::query()
                    ->where('metadata_id', $metadataId)
                    ->where('progress', $entry['progress'])
                    ->first();

                if ($existing) {
                    $existing->increment('count');
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

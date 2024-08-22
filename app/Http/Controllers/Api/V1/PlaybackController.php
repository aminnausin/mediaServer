<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Playback;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlaybackStoreRequest;
use App\Models\Metadata;
use App\Traits\HttpResponses;

class PlaybackController extends Controller
{
    use HttpResponses;

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaybackStoreRequest $request)
    {
        try {
            $validated = $request->validated($request->all());
            $metadata = Metadata::where('id', $request->metadata_id)->first();

            if(!$metadata || !$metadata->video()) return $this->error(null, 'Video metadata does not exist', 404);

            $playback = Playback::create($validated);

            return $this->success($playback); // new MetadataResource($metadata)
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create playback record. Error: ' . $th->getMessage(), 500);
        }
    }
}

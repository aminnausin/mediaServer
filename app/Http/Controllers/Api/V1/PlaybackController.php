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

    public function __construct()
    {
        $this->middleware('throttle:200,1')->only(['store']);
    }

    /**
     * Display a listing of the resource.
     * Get all playback data points from metadata ID for a video
     */
    public function show(int $id)
    {
        // $request->query('param');

        try {
            return Playback::where('metadata_id', $id)->orderBy('progress', 'asc')->get();
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get playback data. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaybackStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $playback = [];
            $lastId = -1;

            foreach ($request->entries as $entry) {
                if ($lastId != $entry['metadata_id']) {
                    $metadata = Metadata::where('id', $entry['metadata_id'])->first();
                    if (!$metadata || !$metadata->video()) return $this->error(null, 'Video metadata does not exist', 404);

                    $lastId = $entry['metadata_id'];
                }

                $existing = Playback::where('metadata_id', $entry['metadata_id'])->where('progress', $entry['progress'])->first();

                if ($existing) {
                    $validated['count'] = $existing->count + 1;
                    $existing->update($validated);
                    return $this->success($existing);
                }
                $result = Playback::create($entry);
                array_push($playback, $result);
            }

            return $this->success($playback);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create playback record. Error: ' . $th->getMessage(), 500);
        }
    }
}

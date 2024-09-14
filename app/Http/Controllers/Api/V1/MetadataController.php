<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Metadata;
use App\Http\Controllers\Controller;
use App\Http\Requests\MetadataStoreRequest;
use App\Http\Requests\MetadataUpdateRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class MetadataController extends Controller
{
    use HttpResponses;
    /**
     * Store a newly created resource in storage.
     */
    public function store(MetadataStoreRequest $request)
    {
        
        try {
            $validated = $request->validated();

            $existingData = Metadata::where('video_id', $request->video_id)->first();
            if($existingData) return $this->error($existingData, 'Metadata already exists for this video!', 500);
            
            $video = Video::where('id', $request->video_id)->first();
            if(!$video) return $this->error(null, 'Video does not exist', 404);

            $validated['editor_id'] = Auth::user()->id;
            $validated['composite_id'] = $video->folder->path . "/" . basename($video->path);

            $metadata = Metadata::create($validated);

            return $this->success(new VideoResource($metadata->video), $validated); // new MetadataResource($metadata)
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create metadata. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetadataUpdateRequest $request, Metadata $metadata)
    {
        try {
            $validated = $request->validated();
            $validated['editor_id'] = Auth::user()->id;
            $metadata->update($validated);

            return $this->success(new VideoResource($metadata->video), $validated);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to edit video metadata. Error: ' . $th->getMessage(), 500);
        }
        
    }
}

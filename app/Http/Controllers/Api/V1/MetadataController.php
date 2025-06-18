<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LyricsUpdateRequest;
use App\Http\Requests\MetadataStoreRequest;
use App\Http\Requests\MetadataUpdateRequest;
use App\Http\Resources\MetadataResource;
use App\Http\Resources\VideoResource;
use App\Models\Metadata;
use App\Models\Video;
use App\Models\VideoTag;
use App\Traits\HasModelHelpers;
use App\Traits\HasTags;
use App\Traits\HttpResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class MetadataController extends Controller {
    use HasTags;
    use HttpResponses;
    use HasModelHelpers;

    public function show($id) {
        $metadata = Metadata::with(['videoTags.tag'])->findOrFail($id);
        return $this->success(new MetadataResource($metadata));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetadataStoreRequest $request) {
        $validated = $request->validated();

        $video = Video::findOrFail($validated['video_id']);

        $compositeId = $video->folder->path . '/' . basename($video->path);
        $existing = Metadata::where('composite_id', $compositeId)->first();
        if ($this->conflictsWithAnother("video_id", $existing, $validated['video_id'])) {
            return $this->error($existing, 'Metadata with generated unique id already exists for another media!', 500);
        }

        $validated['editor_id'] = Auth::id();
        $validated['composite_id'] = $compositeId;

        $metadata = $existing
            ? $this->updateExisting($existing, $validated, $request)
            : Metadata::create($validated);

        $this->generateTagRelationships($metadata->id, $request->video_tags, $request->deleted_tags, 'metadata_id', VideoTag::class);
        return $this->success(new VideoResource($metadata->video), $validated);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetadataUpdateRequest $request, Metadata $metadata) {
        $validated = $request->validated();

        $validated['editor_id'] = Auth::id();
        $metadata->update($validated);

        $this->generateTagRelationships($metadata->id, $request->video_tags, $request->deleted_tags, 'metadata_id', VideoTag::class);

        return $this->success(new VideoResource($metadata->video), $validated);
    }

    public function updateLyrics(LyricsUpdateRequest $request, Metadata $metadata) {
        try {
            $validated = $request->validated();

            if (empty($metadata->video)) {
                throw new ModelNotFoundException('Song does not exist');
            }

            unset($validated['track']);

            $validated['editor_id'] = Auth::id();
            $metadata->update($validated);

            return response()->json(new VideoResource($metadata->video), 200);
        } catch (\Throwable $th) {
            return $this->error($request, 'Unable to edit song. Error: ' . $th->getMessage(), 500);
        }
    }
}

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
use App\Traits\HasTags;
use App\Traits\HttpResponses;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MetadataController extends Controller {
    use HasTags;
    use HttpResponses;
    use LogsModelChanges;

    public function show($id) {
        $metadata = Metadata::with(['videoTags.tag'])->findOrFail($id);

        return response()->json(new MetadataResource($metadata));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetadataStoreRequest $request) {
        $validated = $request->validated();

        $video = Video::findOrFail($validated['video_id']);

        $validated['editor_id'] = Auth::id();
        $validated['edited_at'] = now();
        $validated['composite_id'] = $video->composite_id;

        $metadata = Metadata::updateOrCreate(
            ['composite_id' => $validated['composite_id']],
            $validated
        );

        $this->generateTagRelationships($metadata->id, $request->video_tags, $request->deleted_tags, 'metadata_id', VideoTag::class);

        return response()->json(new VideoResource($this->eagerLoadVideo($video)));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetadataUpdateRequest $request, Metadata $metadata) {
        $validated = $request->validated();
        $metadata->fill($validated);

        $tagsChanged = $this->generateTagRelationships($metadata->id, $request->video_tags, $request->deleted_tags, 'metadata_id', VideoTag::class);

        if ($metadata->isDirty() || $tagsChanged) {
            $metadata->fill(['editor_id' => Auth::id(), 'edited_at' => now()]);

            $this->logModelChanges($metadata, ['tags_changed' => $tagsChanged]);

            $metadata->save();
        }

        try {
            $video = Video::findOrFail($metadata->video_id);

            return response()->json(new VideoResource($this->eagerLoadVideo($video)));
        } catch (ModelNotFoundException $_) {
            Log::warning('Media not found when submitting metadata edit', ['metadata_id' => $metadata->id, 'video_id' => $metadata->video_id, 'composite_id' => $metadata->composite_id]);

            return response()->noContent();
        } catch (\Exception $th) {
            return $this->error($request, 'Unable to update metadata. Error: ' . $th->getMessage(), 500);
        }
    }

    public function updateLyrics(LyricsUpdateRequest $request, Metadata $metadata) {
        if ($metadata->video_id === null) {
            throw new ModelNotFoundException('Song does not exist');
        }

        $validated = $request->validated();
        $validated['title'] = $validated['track']; // ?? Track is unused ? I think this is by design? The title is displayed in more places than just the lyrics editor so it should not be changed by the external api.

        $metadata->fill($validated);

        if ($metadata->isDirty()) {
            $metadata->fill(['editor_id' => Auth::id(), 'edited_at' => now()]);
            $this->logModelChanges($metadata);
            $metadata->save();
        }

        $video = Video::findOrFail($metadata->video_id);

        return response()->json(new VideoResource($this->eagerLoadVideo($video)));
    }

    private function eagerLoadVideo(Video $video): Video {
        return $video->load(['metadata', 'metadata.subtitles', 'metadata.videoTags']);
    }
}

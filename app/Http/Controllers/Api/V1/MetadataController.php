<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetadataStoreRequest;
use App\Http\Requests\MetadataUpdateRequest;
use App\Http\Resources\MetadataResource;
use App\Http\Resources\VideoResource;
use App\Models\Metadata;
use App\Models\Video;
use App\Models\VideoTag;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class MetadataController extends Controller {
    use HttpResponses;

    public function show($id) {
        try {
            return $this->success(
                new MetadataResource(Metadata::with(['videoTags.tag'])->where('id', $id)->first())
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get data. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetadataStoreRequest $request) {
        try {
            $validated = $request->validated();

            $video = Video::where('id', $request->video_id)->first();
            if (! $video) {
                return $this->error(null, 'Video does not exist', 404);
            }

            $existing = Metadata::where('composite_id', $video->folder->path . '/' . basename($video->path))->first();
            if ($existing && $existing->video_id != $request->video_id) {
                return $this->error($existing, 'Metadata with generated unique id already exists for another video!', 500);
            }

            $validated['editor_id'] = Auth::id();
            $validated['composite_id'] = $video->folder->path . '/' . basename($video->path);

            if ($existing) {
                $existing->update($validated);

                $this->generateTags($existing->id, $request->video_tags, $request->deleted_tags);

                return $this->success(new VideoResource($existing->video), $validated); // new MetadataResource($metadata)
            }

            $metadata = Metadata::create($validated);

            $this->generateTags($metadata->id, $request->video_tags, $request->deleted_tags);

            return $this->success(new VideoResource($metadata->video), $validated); // new MetadataResource($metadata)
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create metadata. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MetadataUpdateRequest $request, Metadata $metadata) {
        try {
            $validated = $request->validated();
            $validated['editor_id'] = Auth::id();
            $metadata->update($validated);

            $this->generateTags($metadata->id, $request->video_tags, $request->deleted_tags);

            return $this->success(new VideoResource($metadata->video), $validated);
        } catch (\Throwable $th) {
            return $this->error($request, 'Unable to edit video metadata. Error: ' . $th->getMessage(), 500);
        }
    }

    public function generateTags($metadata_id, $video_tags, $deleted_tags = []) {
        foreach ($video_tags as $tag) {
            VideoTag::firstOrCreate(['tag_id' => $tag['id'], 'metadata_id' => $metadata_id]);
        }

        VideoTag::destroy($deleted_tags);
    }
}

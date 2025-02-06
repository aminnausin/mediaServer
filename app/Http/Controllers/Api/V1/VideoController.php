<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoCollectionRequest;
use App\Http\Requests\VideoUpdateRequest;
use App\Http\Resources\VideoResource;
use App\Models\Record;
use App\Models\Video;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function getFrom(VideoCollectionRequest $request) {
        try {
            $result = VideoResource::collection(Video::where('folder_id', $request->folder_id)->get());

            return $this->success($result);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get videos. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $video_id
     * @return \Illuminate\Http\Response
     */
    public function show(Video $video) {
        return new VideoResource($video);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(VideoUpdateRequest $request, Video $video) {
        try {
            if (Auth::check()) {
                $validated = $request->validated();
                $video->update($validated);

                return $this->success(new VideoResource($video));
            } else {
                return $this->error(new VideoResource($video), 'Unauthenticated', 401);
            }
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to edit video. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Update view counter
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function watch(Request $request, Video $video) {
        $metadata = $video->metadata;

        // Only update view count via metadata if it exists (ie do not reset)
        if (isset($metadata->view_count)) {
            $metadata->update(['view_count' => $metadata->view_count + 1]);
        } else {
            $metadata->update(['view_count' => Record::where('video_id', $video->id)->whereNull('metadata_id')->count() + ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0) + 1]);
        }

        return $this->success(new VideoResource($video->load('metadata.videoTags.tag')));
    }
}

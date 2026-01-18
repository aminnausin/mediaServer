<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoCollectionRequest;
use App\Http\Resources\VideoResource;
use App\Models\Record;
use App\Models\Subtitle;
use App\Models\Video;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            Log::alert('RESETING VIEWS ON METADATA:' . $metadata->id, [$metadata]);
            $metadata->update(['view_count' => ($metadata->id ? Record::where('metadata_id', $metadata->id)->count() : 0) + 1]);
        }

        return $this->success(new VideoResource($video->load([
            'metadata.videoTags.tag',
            'metadata.subtitles' => function ($q) {
                $q->select(Subtitle::getVisibleFields());
            },
        ])));
    }
}

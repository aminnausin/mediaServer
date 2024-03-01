<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FolderCollectionRequest;
use App\Http\Requests\VideoCollectionRequest;
use App\Http\Resources\VideoResource;
use App\Models\Folder;
use App\Models\Video;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function getFrom(VideoCollectionRequest $request)
    {
        try {

            $result = VideoResource::collection( Video::where('folder_id', $request->folder_id)->get() );

            return $this->success($result);
                
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get videos. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param int $video_id
     * @return \Illuminate\Http\Response
     */
    public function show(Video $video)
    {
        return new VideoResource($video);
    }
}

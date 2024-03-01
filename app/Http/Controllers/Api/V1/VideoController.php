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
    public function update(FolderCollectionRequest $request)
    {
        try {
            return $this->success(
                VideoResource::collection(
                    Video::where('folder_id', $request->folder_id)->get()    
                ),$request->folder_id
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get videos. Error: ' . $th->getMessage(), 500);
        }
    }

    // /**
    //  * Display the specified resource.
    //  * 
    //  * @param int $video_id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function getOne(Video $video)
    // {
    //     return RecordResource::collection(
    //         Record::where('user_id', Auth::user()->id)->where('video_id', $video->id)->get()
    //     );
    // }
}

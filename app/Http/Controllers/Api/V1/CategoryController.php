<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     * Get all folders with video counts from category ID
     */
    public function getFrom(CategoryRequest $request)
    {
        $request->validated($request->all());

        try {
            return $this->success(
                FolderResource::collection(
                    Folder::where('category_id', $request->category_id)->withCount(['videos'])->get()    
                )
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get folders. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     * Get Folder with video count from folder ID
     * 
     * @param int $video_id
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder)
    {
        return new FolderResource(Folder::withCount(['videos'])->find($folder->id));
    }
}

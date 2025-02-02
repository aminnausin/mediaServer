<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FolderCollectionRequest;
use App\Http\Resources\FolderResource;
use App\Models\Category;
use App\Models\Folder;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     * Get all folders with video counts from category ID
     */
    public function getFrom(FolderCollectionRequest $request) {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        if (! $category || ($category->is_private && (! $request->user('sanctum') || (Auth::user() && Auth::user()->id !== 1)))) {
            return $this->error(null, 'Access to this folder is forbidden', 403);
        }

        try {
            return $this->success(
                FolderResource::collection(
                    Folder::with(['videos.metadata', 'series'])->where('category_id', $validated['category_id'])->orderBy('id')->get()
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
     * @param  int  $video_id
     * @return \Illuminate\Http\Response
     */
    public function show(Folder $folder) {
        $folder->load(['videos.metadata', 'series']);

        return new FolderResource($folder);
    }
}

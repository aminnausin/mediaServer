<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FolderCollectionRequest;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function getFrom(FolderCollectionRequest $request)
    {
        $request->validated($request->all());

        try {
            return $this->success(
                Folder::where('category_id', $request->category_id)->first()
                //FolderResource::collection(
                    // Folder::withCount(['videos'])->having('category_id', '=', $request->category_id)->get()    
                    //Folder::where('category_id', $request->category_id)->withCount(['videos'])->get()    
                //)
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get folders. Error: ' . $th->getMessage(), 500);
        }
    }
}

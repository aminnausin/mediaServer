<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Series;
use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Folder;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    use HttpResponses;

    /* User can: 
     *
     * - List all series (to search ? admin page ?)
     * - Store (Create series if row for folder does not already exist)
     * - Update (User updates details)
     * 
     * User cannot:
     * - Show (Series data is added to each folder. Theres no reason to get one row on its own)
     * - Delete (User should not be able to delete series row | maybe admin can but thats later)
     */
        
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return $this->success(
                SeriesResource::collection(
                    Series::all()   
                )
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of series. Error: ' . $th->getMessage(), 500);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeriesStoreRequest $request)
    {
        if(!Auth::user()) $this->error(null, 'Unauthenticated', 401);
        try {
            $request->validated($request->all());
            $folder = Folder::where('id', $request->folder_id)->first();

            if(!$folder) return $this->error(null, 'Folder does not exist', 404);

            $request['editor_id'] = Auth::user()->id;
            $series = Series::create($request->all());

            return $this->success(new SeriesResource($series));
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create series. Error: ' . $th->getMessage(), 500);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeriesUpdateRequest $request, Series $series)
    {
        if(!Auth::user()) $this->error(null, 'Unauthenticated', 401);
        try {
            $validated = $request->validated();
            $series->update($validated);

            return $this->success(new SeriesResource($series));
                
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to edit series. Error: ' . $th->getMessage(), 500);
        }
    }
}

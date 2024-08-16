<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Series;
use App\Http\Requests\UpdateSeriesRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\SeriesResource;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreSeriesRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Series $series)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Series $series)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSeriesRequest $request, Series $series)
    {
        try {
            if(Auth::check()){
                $validated = $request->validated();
                $series->update($validated);
    
                return $this->success(new SeriesResource($series));
            }
            else{
                return $this->error(new SeriesResource($series), 'Unauthenticated', 401);
            }
                
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to edit video. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(Series $series)
    // {
    //     //
    // }
}

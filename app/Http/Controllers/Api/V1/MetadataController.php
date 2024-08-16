<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Metadata;
use App\Http\Requests\StoreMetadataRequest;
use App\Http\Requests\UpdateMetadataRequest;
use App\Http\Controllers\Controller;

class MetadataController extends Controller
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
    // public function store(StoreMetadataRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Metadata $metadata)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Metadata $metadata)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMetadataRequest $request, Metadata $metadata)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Metadata $metadata)
    {
        //
    }
}

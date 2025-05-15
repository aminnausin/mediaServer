<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Folder;
use App\Models\FolderTag;
use App\Models\Series;
use App\Traits\HasTags;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller {
    use HasTags;
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
    public function index() {
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
    public function store(SeriesStoreRequest $request) {
        try {
            $validated = $request->validated();

            $folder = Folder::where('id', $request->folder_id)->first();
            if (! $folder) {
                return $this->error(null, 'Folder does not exist', 404);
            }

            $existing = Series::where('composite_id', $folder->path)->first();
            if ($existing && $existing->folder_id != $request->folder_id) {
                return $this->error($existing, 'Series already exists for another folder!', 500);
            }

            $validated['editor_id'] = Auth::id();
            $validated['composite_id'] = $folder->path;

            if ($existing) {
                $existing->update($validated);

                $this->generateTagRelationships($existing->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

                return $this->success(new SeriesResource($existing), $validated); // new MetadataResource($metadata)
            }

            $series = Series::create($validated);

            $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

            return $this->success(new SeriesResource($series));
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create series. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeriesUpdateRequest $request, Series $series) {
        try {
            $validated = $request->validated();
            $validated['editor_id'] = Auth::id();
            $series->update($validated);

            $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

            return $this->success(new SeriesResource($series));
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to edit series. Error: ' . $th->getMessage(), 500);
        }
    }
}

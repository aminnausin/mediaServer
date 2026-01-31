<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Folder;
use App\Models\FolderTag;
use App\Models\Series;
use App\Traits\HasModelHelpers;
use App\Traits\HasTags;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller {
    use HasModelHelpers;
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
        return $this->success(
            SeriesResource::collection(
                Series::all()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SeriesStoreRequest $request) {
        $validated = $request->validated();

        $folder = Folder::findOrFail($validated['folder_id']);

        $compositeId = $folder->path;
        $existing = Series::where('composite_id', $compositeId)->first();
        if ($this->conflictsWithAnother('folder_id', $existing, $validated['folder_id'])) {
            return $this->error($existing, 'Series already exists for another folder!', 500);
        }

        $validated['editor_id'] = Auth::id();
        $validated['edited_at'] = now();
        $validated['composite_id'] = $compositeId;
        $series = $existing
            ? $this->updateExisting($existing, $validated, $request)
            : Series::create($validated);

        $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

        return response()->json(new SeriesResource($series));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeriesUpdateRequest $request, Series $series) {
        $validated = $request->validated();
        $validated['editor_id'] = Auth::id();
        $validated['edited_at'] = now();
        $series->update($validated);

        $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

        return response()->json(new SeriesResource($series));
    }
}

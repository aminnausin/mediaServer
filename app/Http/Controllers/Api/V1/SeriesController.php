<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Images\ImageUpdateData;
use App\Enums\ImageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Series\SeriesImageUpdateRequest;
use App\Http\Requests\SeriesStoreRequest;
use App\Http\Requests\SeriesUpdateRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Folder;
use App\Models\FolderTag;
use App\Models\Series;
use App\Services\Images\ImageService;
use App\Traits\HasModelHelpers;
use App\Traits\HasTags;
use App\Traits\HttpResponses;
use App\Traits\LogsModelChanges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SeriesController extends Controller {
    use HasModelHelpers;
    use HasTags;
    use HttpResponses;
    use LogsModelChanges;

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
            ? $this->updateExisting($existing, $validated, true)
            : Series::create($validated);

        $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

        return response()->json(new SeriesResource($series));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SeriesUpdateRequest $request, Series $series) {
        $validated = $request->validated();
        $series->fill($validated);


        $tagsChanged = $this->generateTagRelationships($series->id, $request->tags, $request->deleted_tags, 'series_id', FolderTag::class);

        if ($series->isDirty() || $tagsChanged) {
            $user = Auth::user();
            $series->fill(['editor_id' => $user->id, 'edited_at' => now()]);

            $this->logModelChanges($series, ['tags_changed' => $tagsChanged], $user);

            $series->save();
        }

        return response()->json(new SeriesResource($series));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateDownloadSettings(Request $request, Series $series) {
        if (Auth::id() !== 1) {
            return $this->forbidden();
        }

        $validated = $request->validate([
            'downloads_enabled' => 'sometimes|boolean',
        ]);
        $series->update($validated);

        return response($series->only(['downloads_enabled']));
    }

    public function updateImages(SeriesImageUpdateRequest $request, Series $series, ImageService $imageService) {
        $this->authorize('editor');

        $user = Auth::user();

        $imageUpdateData = ImageUpdateData::fromRequest($request, $user, Gate::allows('admin'));
        $image = $imageService->resolveUpdatedImage($series, $imageUpdateData);

        if ($imageUpdateData->mode !== 'remove' && ! $image) {
            abort(422, 'Image could not be resolved.');
        }

        match ($imageUpdateData->imageType) {
            ImageType::POSTER => $series->primary_poster_id = $image?->id,
            default => null,
        };

        if ($series->isDirty()) {
            $series->fill(['editor_id' => $imageUpdateData->user->id, 'edited_at' => now()]);
            $this->logModelChanges($series, ['request' => ['type' => 'update images', ...$request->validated()]], $imageUpdateData->user);
            $series->save();
        }

        $imageService->softDeleteImages($series, $imageUpdateData);
        $series->refresh();

        return response()->json(new SeriesResource($series));
    }
}

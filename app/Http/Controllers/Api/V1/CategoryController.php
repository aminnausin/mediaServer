<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Folder;
use App\Traits\HasModelHelpers;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller {
    use HasModelHelpers;
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = Category::orderBy('name');

        if (! Gate::allows('admin')) {
            $categories->where('is_private', false);
        }

        $categories->with(['folders.series', 'folders.series.primaryPoster']);

        return response()->json(CategoryResource::collection($categories->get()));
    }

    /**
     * Display the specified resource.
     * Get Category with count of folders with the category id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category) {
        if (! Gate::allows('admin') && $category->is_private) {
            abort(403);
        }

        $category->load(['folders.series.folderTags']);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function setDefaultFolder(CategoryUpdateRequest $request, Category $category) {
        if (Auth::id() !== 1) {
            return $this->forbidden();
        }

        $validated = $request->validated();

        $folder = Folder::findOrFail($validated['default_folder_id']);

        if ($this->conflictsWithAnother('category_id', $folder, $category->id)) {
            return $this->error($category, 'Folder cannot be assigned to library!', 500);
        }

        $validated['editor_id'] = Auth::id();
        $category->update($validated);

        return $this->success($validated);
    }

    public function updateSettings(Request $request, Category $category) {
        $this->authorize('admin');

        $validated = $request->validate([
            'is_private' => 'sometimes|boolean',
            'downloads_enabled' => 'sometimes|boolean',
            'downloads_require_auth' => 'sometimes|boolean',
            'storyboard_enabled' => 'sometimes|boolean',
        ]);

        $category->update(array_merge($validated, ['editor_id' => Auth::id()]));

        return response($category->only(array_keys($validated)));
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryPrivacyUpdateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Folder;
use App\Traits\HasModelHelpers;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller {
    use HasModelHelpers;
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = Category::orderBy('name');
        $userId = Auth::id();

        if ($userId !== 1) {
            $categories->where('is_private', false);
        }

        $categories = $categories->with(['folders.series']);

        return $this->success(
            $userId
                ? CategoryResource::collection($categories->get())
                : [new CategoryResource($categories->first())]
        );
    }

    /**
     * Display the specified resource.
     * Get Category with count of folders with the category id
     *
     * @param  int  $category_id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category) {
        $category->load(['folders.series']);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $category_id
     */
    public function update(CategoryUpdateRequest $request, Category $category) {
        if (Auth::id() !== 1) {
            return $this->forbidden();
        }

        $validated = $request->validated();

        $folder = Folder::findOrFail($validated['default_folder_id']);

        if ($this->conflictsWithAnother('category_id', $folder, $category->id)) {
            return $this->error($category, 'Folder cannot be assigned to category!', 500);
        }

        $validated['editor_id'] = Auth::id();
        $category->update($validated);

        return $this->success($validated);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $category_id
     */
    public function updatePrivacy(CategoryPrivacyUpdateRequest $request, Category $category) {
        if (Auth::id() !== 1) {
            return $this->forbidden();
        }

        $validated = $request->validated();
        $category->is_private = $validated['is_private'] ?? $category->is_private;
        $category->editor_id = Auth::id();
        $category->save();

        return $this->success($validated);
    }
}

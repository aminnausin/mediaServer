<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Folder;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        if (! Auth::user()) {
            $this->unauthorized();
        }
        try {
            $categories = Category::orderBy('name');

            if (Auth::user()->id !== 1) {
                $categories->where('is_private', false);
            }

            return $this->success(
                CategoryResource::collection(
                    $categories->with(['folders.series'])->get()
                )
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of categories. Error: ' . $th->getMessage(), 500);
        }
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
        if (! Auth::user() || Auth::user()->id !== 1) {
            $this->unauthorized();
        }

        try {
            $validated = $request->validated();

            $folder = Folder::where('id', $validated['default_folder_id'])->first();
            if (! $folder || $folder->category_id != $category->id) {
                return $this->error($category, 'Folder cannot be assigned to category!', 500);
            }

            $validated['editor_id'] = Auth::id();
            $category->update($validated);

            return $this->success($validated);
        } catch (\Throwable $th) {
            return $this->error($request, 'Unable to edit category. Error: ' . $th->getMessage(), 500);
        }
    }
}

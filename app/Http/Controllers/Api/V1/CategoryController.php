<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        try {
            if (!Auth::user() || Auth::user()->id !== 1) {
                abort(403, 'Unauthorized action.');
            }

            return $this->success(
                CategoryResource::collection(
                    Category::all()->sortBy('name')
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
     * @param int $category_id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category) {
        return new CategoryResource($category);
    }
}

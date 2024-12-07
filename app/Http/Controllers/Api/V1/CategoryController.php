<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Traits\HttpResponses;
use App\Models\Category;

class CategoryController extends Controller
{
    use HttpResponses;

    /**
     * Display the specified resource.
     * Get Category with count of folders with the category id
     * 
     * @param int $category_id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }
}

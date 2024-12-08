<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Series;
use App\Http\Controllers\Controller;
use App\Http\Requests\TagStoreRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller {
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
                TagResource::collection(
                    Tag::all()->sortBy('name')
                )
            );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of tags. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagStoreRequest $request) {
        try {
            $validated = $request->validated();

            $existing = Tag::where('name', $request->name)->first();
            if ($existing) return $this->error($existing, 'Tag already exists!', 500);

            $validated['name'] = strtolower($validated['name']);

            $validated['creator_id'] = Auth::id();
            $tag = Tag::create($validated);
            return new TagResource($tag);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to create tag. Error: ' . $th->getMessage() . $request, 500);
        }
    }
}

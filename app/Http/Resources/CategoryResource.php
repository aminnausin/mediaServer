<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CategoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'default_folder_id' => $this->default_folder_id,
            'folders_count' => $this->folders_count ?? 0,
            'folders' => FolderResource::collection($this->folders),
            'videos_count' => $this->videos_count ?? $this->when(true, function () {
                return DB::table('videos')->join('folders', 'videos.folder_id', '=', 'folders.id')->join('categories', 'folders.category_id', '=', 'categories.id')->where('categories.id', $this->id)->count('videos.id');
            }),
            'created_at' => $this->created_at,
            'last_scan' => $this->last_scan,
        ];
    }
}

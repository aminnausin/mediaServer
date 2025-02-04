<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'videos_count' => $this->videos_count ?? 0,
            'created_at' => $this->created_at,
            'last_scan' => $this->last_scan,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderTagResource extends JsonResource {
    // protected static $tagCache = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        if (! $this->relationLoaded('tag')) {
            $this->loadMissing('tag');
        }

        $tag = $this->tag;

        return [
            'folder_tag_id' => $this->id,
            'name' => $tag->name,
            'id' => $this->tag_id,
        ];
    }
}

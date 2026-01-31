<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title ?? $this->video->name,
                'description' => $this->description,
                'season' => $this->season,
                'episode' => $this->episode,
                'duration' => $this->duration,
                'view_count' => $this->view_count,
                'file_size' => $this->file_size,
                'released_at' => $this->released_at,            // user provided metadata
                'edited_at' => $this->edited_at,                // last user edit time
                'file_modified_at' => $this->file_modified_at,  // file_mtime from disk (required)
                'updated_at' => $this->updated_at,              // db update time
            ],
            'relationships' => [
                'video_id' => $this->video_id,
                'editor_id' => $this->editor_id,
                'video_tags' => VideoTagResource::collection($this->videoTags),
            ],
        ];
    }
}

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
                'date_released' => $this->date_released,
                'date_updated' => $this->updated_at,
                'date_uploaded' => $this->date_uploaded,
            ],
            'relationships' => [
                'video_id' => $this->video_id,
                'editor_id' => $this->editor_id,
                'video_tags' => VideoTagResource::collection($this->videoTags),
            ],
        ];
    }
}

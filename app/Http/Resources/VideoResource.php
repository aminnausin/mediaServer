<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $metadata = $this->metadata;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            'title' => $metadata?->title ?: $this->title ?: $this->name,
            'description' => $metadata?->description ?: $this->description, // ?: $this->folder->series->description
            'duration' => $metadata?->duration ?: $this->duration,
            'episode' => $metadata?->episode ?: $this->episode,
            'season' => $metadata?->season ?: $this->season,
            'view_count' => $metadata?->view_count ?: $this->view_count,
            'file_size' => $metadata?->file_size ?: null,
            'video_tags' => VideoTagResource::collection($metadata?->videotags ?: []),
            'date_released' => $metadata?->date_released ?: null,
            'date_updated' => $metadata?->updated_at ?: null,
            'date_uploaded' => $metadata?->date_uploaded ?: null,
            'metadata' => $metadata,
        ];
    }
}

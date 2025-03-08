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
        if (! $this->relationLoaded('metadata')) {
            $this->loadMissing('metadata');
        }

        $metadata = $this->metadata;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            'title' => $metadata?->title ?: $this->name,
            'description' => $metadata?->description, // ?: $this->folder->series->description
            'duration' => $metadata?->duration,
            'episode' => $metadata?->episode,
            'season' => $metadata?->season,
            'view_count' => $metadata?->view_count,
            'file_size' => $metadata?->file_size ?: null,
            'video_tags' => VideoTagResource::collection($metadata->videoTags ?? []),
            'date_released' => $metadata?->date_released ?: null,
            'date_updated' => $metadata?->updated_at ?: null,
            'date_uploaded' => $metadata?->date_uploaded ?: null,
            'metadata' => $metadata,
        ];
    }
}

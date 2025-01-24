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
            // 'path' => $this->path,
            // 'name' => $this->name,
            // 'date' => $this->date,

            // 'folder_id' => (string)$this->folder->id,
            // 'metadata' => new MetadataResource($metadata),

            // 'attributes' => [
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            // 'date_raw' => $this->date_raw,
            'title' => ($metadata ? $metadata->title : $this->title) ?? $this->name,
            'description' => $metadata ? $metadata->description : $this->description, // ?? $this->folder->series->description
            'duration' => $metadata ? $metadata->duration : $this->duration,
            'episode' => $metadata ? $metadata->episode : $this->episode,
            'season' => $metadata ? $metadata->season : $this->season,
            'view_count' => $metadata ? $metadata->view_count : $this->view_count,
            'file_size' => $metadata ? $metadata->file_size : null,
            // 'tags' => $metadata->tags ?? '',
            'video_tags' => $metadata ? VideoTagResource::collection($metadata->videotags) : [],
            'date_released' => $metadata ? $metadata->date_released : null,
            'date_updated' => $metadata ? $metadata->updated_at : null,
            'date_uploaded' => $metadata ? $metadata->date_uploaded : null,
            // ],
            // 'relationships' => [
            // 'folder_id' => (string)$this->folder->id,
            'metadata' => $metadata,
            // 'editor' => $metadata ? $metadata->editor : null
            // ]
        ];
    }
}

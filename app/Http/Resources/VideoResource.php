<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $metadata = $this->metadata;
        return [
            'id' => (string)$this->id,
            // 'attributes' => [
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            'date_raw' => $this->date_raw,
            'title' => ($metadata ? $this->metadata->title : $this->title) ?? $this->name,
            'description' => $metadata ? $this->metadata->description : $this->description, // ?? $this->folder->series->description
            'duration' => $metadata ? $this->metadata->duration : $this->duration,
            'episode' => $metadata ? $this->metadata->episode : $this->episode,
            'season' => $metadata ? $this->metadata->season : $this->season,
            'view_count' => $metadata ? $this->metadata->view_count : $this->view_count,
            'tags' => $this->metadata->tags ?? '',
            'video_tags' => $this->metadata ? VideoTagResource::collection($this->metadata->videotags) : [],
            'date_released' => $this->metadata ? $this->metadata->date_released : null,
            'date_updated' => $this->metadata ? $this->metadata->updated_at : null,
            // ],
            // 'relationships' => [
            'folder_id' => (string)$this->folder->id,
            'metadata' => new MetadataResource($metadata),
            'editor' => $metadata ? $this->metadata->editor : null
            // ]
        ];
    }
}

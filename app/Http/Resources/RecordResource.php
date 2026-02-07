<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $metadata = $this->relationLoaded('metadata') ? $this->metadata : null;
        $video = $metadata?->video;
        $folder = $video?->folder;
        $category = $folder?->category;

        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'metadata' => $metadata,
            'category' => $category,
            'video_id' => $metadata?->video_id,
            'video_name' => $metadata?->title ?? $video?->name,
            'folder_name' => $folder?->name ?? 'Unknown',
            'file_name' => $this->name ?? null,
        ];
    }
}

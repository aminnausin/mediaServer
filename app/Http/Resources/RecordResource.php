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
        $metadata = $this->metadata;
        $video = $metadata?->video;
        $folder = $video?->folder;
        $category = $folder?->category;

        return [
            'id' => (string) $this->id,
            'attributes' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'folder' => $folder ?? ['name' => 'Unknown'],
                'metadata' => $metadata,
                'category' => $category,
                'video_id' => $this->video_id ?? $metadata->video_id ?? null,
                'video_name' => $metadata->title ?? $video->name ?? $this->name ?? 'Deleted',
                'file_name' => $this->name,
            ],
        ];
    }
}

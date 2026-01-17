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

        if (! $this->relationLoaded('metadata.subtitles')) {
            $this->loadMissing('metadata.subtitles');
        }

        $metadata = $this->metadata;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            'title' => $metadata?->title ?: $this->name,
            'description' => $metadata?->description, // ?: $this->folder->series->description
            'duration' => $metadata?->duration,
            'episode' => $metadata?->episode,
            'season' => $metadata?->season,
            'artist' => $metadata?->artist,
            'album' => $metadata?->album,
            'view_count' => $metadata?->view_count ?? 0,
            'file_size' => $metadata?->file_size ?: null,
            'video_tags' => VideoTagResource::collection($metadata->videoTags ?? []),
            'date_created' => $this->created_at, // Date Added to Server
            'date_updated' => $metadata?->updated_at ?: null, // Metadata Last Updated
            'date_released' => $metadata?->date_released ?: null, // User Provided Release Date
            'date_uploaded' => $metadata?->date_uploaded ?: null, // File Last Modified (Should be date_added)
            'metadata' => $metadata,
            'subtitles' => SubtitleResource::collection($metadata->subtitles ?? []),
        ];
    }
}

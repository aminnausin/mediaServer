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
            'intro_start' => $metadata->intro_start,
            'intro_duration' => $metadata->intro_duration,
            'created_at' => $this->created_at, // Date Added to Server
            'updated_at' => $metadata?->updated_at ?: null, // Metadata Last Updated
            'released_at' => $metadata?->released_at ?: null, // User Provided Release Date in F d, Y ?
            'file_modified_at' => $metadata?->first_file_modified_at ?: $metadata?->file_modified_at ?: null, // File Last Modified (Should be date_added)
            'edited_at' => $metadata?->edited_at,
            'metadata' => $metadata,
            'subtitles' => SubtitleResource::collection($metadata->subtitles ?? []),
        ];
    }
}

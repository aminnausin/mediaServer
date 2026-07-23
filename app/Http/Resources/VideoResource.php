<?php

namespace App\Http\Resources;

use App\Http\Resources\Metadata\StoryboardResource;
use App\Models\Metadata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        throw_unless(
            $this->relationLoaded('metadata'),
            \RuntimeException::class,
            'VideoResource requires metadata relation to be eager-loaded.'
        );

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
            'intro_start' => $metadata?->intro_start,
            'intro_duration' => $metadata?->intro_duration,

            'video_tags' => VideoTagResource::collection($metadata?->relationLoaded('videoTags') ? $metadata->videoTags : []),
            'subtitles' => SubtitleResource::collection($metadata?->relationLoaded('subtitles') ? $metadata->subtitles : []),
            'storyboard' => $metadata?->relationLoaded('storyboard') ? new StoryboardResource($metadata->storyboard) : null,

            'view_count' => $metadata?->view_count ?? 0,

            'file_size' => $metadata?->file_size ?: null,

            'created_at' => $this->created_at, // Date Added to Server
            'updated_at' => $metadata?->updated_at ?: null, // Metadata Last Updated
            'released_at' => $metadata?->released_at ?: null, // User Provided Release Date in F d, Y ?
            'file_modified_at' => $metadata?->first_file_modified_at ?: $metadata?->file_modified_at ?: null, // File Last Modified (Should be date_added)
            'edited_at' => $metadata?->edited_at,

            ...($this->getPlaybackProgressStats($metadata)),

            'metadata' => new MetadataResource($metadata),
            'folder_id' => $this->folder_id,
            'url' => $this->relationLoaded('folder') ? "{$this->folder->category_id}/{$this->folder_id}?video={$this->id}" : null,
        ];
    }

    private function getPlaybackProgressStats(?Metadata $metadata) {
        $playbackProgress = $metadata?->relationLoaded('playbackProgress') ? $metadata->playbackProgress : null;

        return [
            'progress_offset' => $playbackProgress?->progress_offset ?? 0,
            'progress_percentage' => $playbackProgress?->progress_percentage ?? 0,
            'progress_created_at' => $playbackProgress?->created_at,
            'progress_updated_at' => $playbackProgress?->updated_at,
            'completion_count' => $playbackProgress?->completion_count ?? 0,
        ];
    }
}

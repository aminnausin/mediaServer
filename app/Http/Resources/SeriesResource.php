<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'folder_id' => $this->folder_id,
            'editor_id' => $this->editor_id,
            'title' => $this->title ?? $this->folder->name,
            'description' => $this->description,
            'studio' => $this->studio,
            'rating' => $this->rating,
            'seasons' => $this->seasons,
            'episodes' => $this->episodes,
            'films' => $this->films,
            'avg_intro_duration' => $this->avg_intro_duration,
            'folder_tags' => $this->whenLoaded('folderTags', fn () => FolderTagResource::collection($this->folderTags), []),
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'edited_at' => $this->edited_at,
            'downloads_enabled' => $this->downloads_enabled,

            'thumbnail_url' => $this->primary_poster_id ? null : $this->thumbnail_url,
            'poster_image' => $this->whenLoaded('primaryPoster', fn () => $this->primaryPoster ? new ImageResource($this->primaryPoster) : null),
            'images' => $this->whenLoaded('images', fn () => ImageResource::collection($this->images), []),
        ];
    }
}

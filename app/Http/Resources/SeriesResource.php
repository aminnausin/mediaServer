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
            'folder_tags' => FolderTagResource::collection($this->folderTags ?? []),
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'thumbnail_url' => $this->thumbnail_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'edited_at' => $this->edited_at,
        ];
    }
}

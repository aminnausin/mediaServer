<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * id                   -> int8 (pk) (index)
     * folder_id            -> int8 (fk) (nullable) (index)
     * editor_id            -> int8 (fk) (nullable)
     * composite_id         -> varchar(255) (index)
     *
     * primary_media_type   -> int2 (enum)
     * total_size           -> int8
     *
     * title                -> varchar(255) (nullable)
     * description          -> text (nullable)
     * studio               -> varchar(255) (nullable)
     * rating               -> int2 (nullable)
     * seasons              -> int4 (nullable)
     * episodes             -> int4 (nullable)
     * films                -> int4 (nullable)
     * thumbnail_url        -> varchar(255) (nullable)
     * raw_thumbnail_url    -> varchar(255) (nullable)
     *
     * started_at           -> date (nullable)
     * ended_at             -> date (nullable)
     *
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     * edited_at            -> timestamp (nullable)
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

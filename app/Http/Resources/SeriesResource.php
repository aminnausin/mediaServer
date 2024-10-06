<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folder_id' => $this->folder ? (string)$this->folder->id : null,
            'editor_id' => $this->editor ? $this->editor->id : null,
            'editor_name' => $this->editor ? $this->editor->name : '',
            'title' => $this->title ?? $this->folder->name,
            'description' => $this->description,
            'studio' => $this->studio,
            'rating' => $this->rating,
            'seasons' => $this->seasons,
            'episodes' => $this->episodes,
            'films' => $this->films,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'thumbnail_url' => $this->thumbnail_url,
            'date_updated' => $this->updated_at,
        ];
    }
}

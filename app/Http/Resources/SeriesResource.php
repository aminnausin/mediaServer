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
        // return parent::toArray($request);
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'title' => $this->title ?? $this->folder->name,
                'description' => $this->description,
                'studio' => $this->studio,
                'rating' => $this->rating,
                'seasons' => $this->seasons,
                'episodes' => $this->episodes, // ?? $this->folder->series->description
                'films' => $this->films,
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'thumbnail_url' => $this->thumbnail_url
            ],
            'relationships' => [
                'folder_id' => (string)$this->folder->id,
                'editor_id' => $this->editor->id,
                'editor_name' => $this->editor->name
            ]
        ];
    }
}

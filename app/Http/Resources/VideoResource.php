<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->name,
                'path' => $this->path,
                'date' => $this->date,
                'date_raw' => $this->date_raw,
                'title' => $this->title ?? $this->name,
                'description' => $this->description, // ?? $this->folder->series->description
                'duration' => $this->duration,
                'episode' => $this->episode,
                'season' => $this->season,
                'view_count' => $this->view_count
            ],
            'relationships' => [
                'folder_id' => (string)$this->folder->id
            ]
        ];
    }
}

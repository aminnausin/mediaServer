<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
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
            'name' => $this->name,
            'path' => $this->path,
            'file_count' => $this->videos->count(),
            'category_id' => (string)$this->category->id,
            'videos' => $this->when($request->videos, function () {
                return VideoResource::collection($this->videos);
            }),
            'series' => new SeriesResource($this->series)
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'file_count' => $this->videos_count ?? $this->series->episodes ?? 0, // $videos->count(),
            'total_size' => $this->series->total_size,
            'category_id' => $this->category_id,
            'videos' => $this->when($request->videos, function () {
                return VideoResource::collection($this->videos);
            }),
            'series' => new SeriesResource($this->series),
            'created_at' => $this->created_at,
        ];
    }
}

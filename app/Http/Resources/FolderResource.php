<?php

namespace App\Http\Resources;

use App\Enums\MediaType;
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
            'title' => $this->series?->title,
            'path' => $this->path,
            'file_count' => $this->videos_count ?? $this->series->episodes ?? 0, // $videos->count(),
            'total_size' => $this->series->total_size,
            'is_majority_audio' => $this->series->primary_media_type->value === MediaType::AUDIO->value,
            'category_id' => $this->category_id,
            'videos' => $this->when($request->videos, function () {
                return VideoResource::collection($this->videos);
            }),
            'series' => new SeriesResource($this->series),
            'scanned_at' => $this->created_at,
            'created_at' => $this->series?->created_at ?? $this->created_at,
            'updated_at' => $this->series?->updated_at ?? $this->updated_at,
        ];
    }
}

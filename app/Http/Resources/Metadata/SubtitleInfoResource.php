<?php

namespace App\Http\Resources\Metadata;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubtitleInfoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'track_id' => $this->track_id,
            'metadata_uuid' => $this->metadata_uuid,
            'language' => $this->language,
            'title' => $this->title,
            'codec' => $this->codec,
            'format' => $this->format,
            'is_default' => $this->is_default,
            'is_forced' => $this->is_forced,
            'path' => $this->path,
            'external_path' => $this->external_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubtitleResource extends JsonResource {
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
            'codec' => $this->codec,
        ];
    }
}

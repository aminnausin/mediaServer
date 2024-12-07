<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaybackResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'attributes' => [
                'progress' => $this->progress
            ],
            'relationships' => [
                'metadata_id' => $this->metadata_id,
                // 'video_id' => $this->metadata ? $this->metadata->video_id : null, ?? dont need ??
            ]
        ];
    }
}

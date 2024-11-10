<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoTagResource extends JsonResource
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
            'name' => $this->tag->name,
            'relationships' => [
                'metadata_id' => $this->metadata ? $this->metadata->id : null,
                'tag_id' => $this->tag ? $this->tag->id : null,
            ]
        ];
    }
}

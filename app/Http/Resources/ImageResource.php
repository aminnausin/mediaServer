<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'path' => asset("/storage/{$this->path}"),
            'type' => $this->image_type->value,
            'source' => $this->image_source->value,
            'blur_hash' => $this->blur_hash,
        ];
    }
}

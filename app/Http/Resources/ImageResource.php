<?php

namespace App\Http\Resources;

use App\Enums\ImageSource;
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
            'type' => $this->image_type,
            'source' => $this->user_id && $this->image_source === ImageSource::DOWNLOADED ? ImageSource::UPLOADED : $this->image_source,
            'blur_hash' => $this->blur_hash,
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => $this->user ? new UserResource($this->user) : null),
            'created_at' => $this->created_at,
            'replaced_at' => $this->replaced_at,
        ];
    }
}

<?php

namespace App\Http\Resources\Metadata;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FontInfoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'size' => $this->size,
            'path' => $this->path ? "/storage/{$this->path}" : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

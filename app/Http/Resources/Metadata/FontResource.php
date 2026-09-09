<?php

namespace App\Http\Resources\Metadata;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FontResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'file_name' => $this->fileName,
            'path' => $this->path ? "/storage/{$this->path}" : null,
        ];
    }
}

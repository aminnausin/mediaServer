<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesSizeHistoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'total_bytes' => $this->total_bytes,
            'file_count' => $this->file_count,
            'recorded_at' => $this->recorded_at,
        ];
    }
}

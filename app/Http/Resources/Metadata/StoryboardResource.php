<?php

namespace App\Http\Resources\Metadata;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryboardResource extends JsonResource {
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'tile_rows' => $this->tile_rows,
            'tile_cols' => $this->tile_cols,
            'tile_width' => $this->tile_width,
            'tile_height' => $this->tile_height,
            'tile_count' => $this->tile_count,
            'interval_seconds' => $this->interval_seconds,
        ];
    }
}

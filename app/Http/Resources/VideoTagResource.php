<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoTagResource extends JsonResource {
    // protected static $tagCache = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // $tag_id = $this->tag_id;

        // if (isset(self::$tagCache[$tag_id])) {
        // $tag = self::$tagCache[$tag_id];
        // } else {
        // $tag = $this->tag;
        // self::$tagCache[$tag_id] = $tag;
        // }

        if (! $this->relationLoaded('tag')) {
            $this->loadMissing('tag');
        }

        $tag = $this->tag;

        return [
            'video_tag_id' => $this->id,
            'name' => $tag->name,
            'id' => $this->tag_id,
        ];
    }
}

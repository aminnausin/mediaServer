<?php

namespace App\Http\Resources\Metadata;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaInfoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        throw_unless(
            $this->relationLoaded('metadata'),
            \RuntimeException::class,
            'VideoResource requires metadata relation to be eager-loaded.'
        );

        $metadata = $this->metadata;

        return [
            'id' => $this->id,
            'name' => $this->name,

            'images' => ImageResource::collection($metadata?->relationLoaded('images') ? $metadata->images : []),
            'subtitles' => SubtitleInfoResource::collection($metadata?->relationLoaded('subtitles') ? $metadata->subtitles : []),
            'fonts' => FontInfoResource::collection($metadata?->relationLoaded('fonts') ? $metadata->fonts : []),
            'storyboard' => $metadata?->relationLoaded('storyboard') ? new StoryboardResource($metadata->storyboard) : null,
            'raw_metadata' => $metadata?->raw_metadata,
        ];
    }
}

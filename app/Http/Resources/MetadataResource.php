<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataResource extends JsonResource
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
            'attributes' => [
                'title' => $this->title ?? $this->video->name,
                'description' => $this->description,
                'season' => $this->season,
                'episode' => $this->episode,
                'duration' => $this->duration,
                'view_count' => $this->view_count,
                'date_released' => $this->date_released,
                'date_updated' => $this->updated_at
            ],
            'relationships' => [
                'video_id' => $this->video ? (string)$this->video->id : null,
                'editor_id' => $this->editor ? $this->editor->id : null,
                'editor_name' => $this->editor ? $this->editor->name : '',
                // 'playback_data' => PlaybackResource::collection($this->playbacks),
                'video_tags' => VideoTagResource::collection($this->videoTags),
            ]
        ];
    }
}

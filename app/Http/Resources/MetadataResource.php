<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetadataResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,

            'video_id' => $this->video_id,
            'editor_id' => $this->editor_id,

            'title' => $this->title,
            'artist' => $this?->artist,
            'album' => $this?->album,
            'lyrics' => $this->lyrics,

            'poster_url' => $this->poster_url,

            'duration' => $this->duration,
            'mime_type' => $this->mime_type,
            'codec' => $this->codec,
            'bitrate' => $this->bitrate,
            'resolution_width' => $this->resolution_width,
            'resolution_height' => $this->resolution_height,
            'frame_rate' => $this->frame_rate,
            'media_type' => $this->media_type,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,              // db update time
            'edited_at' => $this->edited_at,                // last user edit time

            'file_scanned_at' => $this->file_scanned_at,
            // file_modified_at is file_mtime from disk (defaults to first seen date rather than the date of any updated files)
            'file_modified_at' => $this->first_file_modified_at ?: $this->file_modified_at,
            'first_file_modified_at' => $this->first_file_modified_at,
            'subtitles_scanned_at' => $this->subtitles_scanned_at,
        ];
    }
}

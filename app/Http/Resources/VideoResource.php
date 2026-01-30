<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * VIDEO
     * id                   -> int8 (pk) (index)
     * folder_id            -> int8 (fk) (not indexed ???)
     *
     * name                 -> varchar(255)
     * path                 -> varchar(255) (index)
     * date                 -> varchar(255)
     *
     * uuid                 -> uuid (index) (nullable) (un-maintained)
     * created_at           -> timestamp (nullable)
     *
     * title                -> varchar(255) (nullable) (legacy - remove)
     * description          -> varchar(255) (nullable) (legacy - remove)
     * duration             -> int4 (nullable) (legacy - remove)
     * episode              -> int4 (nullable) (legacy - remove)
     * season               -> int4 (nullable) (legacy - remove)
     * view_count           -> int4 (nullable) (legacy - remove)
     *
     *
     *
     * METADATA
     * id                   -> int8 (pk) (index)
     * uuid                 -> uuid (index) (nullable)
     * video_id             -> int8 (fk) (index) (nullable)
     * editor_id            -> int8 (fk) (nullable)
     * composite_id         -> varchar(255) (index)
     * logical_composite_id -> text (index) (generated)
     *
     * title                -> varchar(255) (nullable)
     * description          -> text (nullable)
     * season               -> int4 (nullable)
     * episode              -> int4 (nullable)
     * artist               -> varchar(255) (nullable)
     * album                -> varchar(255) (nullable)
     * lyrics               -> text (nullable)
     * captions             -> text (nullable)
     *
     * raw_thumbnail_url    -> varchar(255) (nullable)
     * poster_url           -> text (nullable)
     *
     * resolution_width     -> int4 (nullable)
     * resolution_height    -> int4 (nullable)
     * frame_rate           -> int4 (nullable)
     * bitrate              -> int8 (nullable)
     * codec                -> varchar(255) (nullable)
     * file_size            -> int8 (nullable)
     *
     * mime_type            -> varchar(255) (nullable)
     * media_type           -> int2 (enum) (default=0)
     *
     * duration             -> int4 (nullable)
     * view_count           -> int4 (nullable) (default=0)
     *
     * date_released        -> date (nullable)
     * date_scanned         -> date (nullable)
     * date_uploaded        -> timestamp (nullable)
     *
     * edited_at            -> timestamptz (nullable)
     * file_scanned_at      -> timestamptz (nullable)
     * subtitles_scanned_at -> timestamp (nullable)
     * created_at           -> timestamp (nullable)
     * updated_at           -> timestamp (nullable)
     *
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        if (! $this->relationLoaded('metadata')) {
            $this->loadMissing('metadata');
        }

        if (! $this->relationLoaded('metadata.subtitles')) {
            $this->loadMissing('metadata.subtitles');
        }

        $metadata = $this->metadata;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'date' => $this->date,
            'title' => $metadata?->title ?: $this->name,
            'description' => $metadata?->description, // ?: $this->folder->series->description
            'duration' => $metadata?->duration,
            'episode' => $metadata?->episode,
            'season' => $metadata?->season,
            'artist' => $metadata?->artist,
            'album' => $metadata?->album,
            'view_count' => $metadata?->view_count ?? 0,
            'file_size' => $metadata?->file_size ?: null,
            'video_tags' => VideoTagResource::collection($metadata->videoTags ?? []),
            'date_created' => $this->created_at, // Date Added to Server
            'date_updated' => $metadata?->updated_at ?: null, // Metadata Last Updated
            'date_released' => $metadata?->date_released ?: null, // User Provided Release Date
            'date_uploaded' => $metadata?->date_uploaded ?: null, // File Last Modified (Should be date_added)
            'edited_at' => $metadata?->edited_at,
            'metadata' => $metadata,
            'subtitles' => SubtitleResource::collection($metadata->subtitles ?? []),
        ];
    }
}

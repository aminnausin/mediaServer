<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource {
    // Mega Yikes AI code
    protected static $categoryCache = [];

    protected static $folderCache = [];

    protected static $metadataCache = [];

    protected static $videoCache = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $metadata = null;
        $video = null;
        $folder = null;
        $category = null;

        $metadataId = $this->metadata_id;

        if (isset(self::$metadataCache[$metadataId])) {
            $metadata = self::$metadataCache[$metadataId];
        } else {
            $metadata = $this->metadata;
            self::$metadataCache[$metadataId] = $metadata;
        }

        $videoId = $metadata?->video_id ?? $this?->video_id;

        if (isset(self::$videoCache[$videoId])) {
            $video = self::$videoCache[$videoId];
        } else {
            $video = $metadata?->video_id ? $metadata?->video : $this->video;
            self::$videoCache[$videoId] = $video;
        }

        $folderId = $video?->folder_id;

        if ($folderId) {
            if (isset(self::$folderCache[$folderId])) {
                $folder = self::$folderCache[$folderId];
            } else {
                $folder = $video->folder;
                self::$folderCache[$folderId] = $folder;
            }

            $categoryId = $folder->category_id;
            if (isset(self::$categoryCache[$categoryId])) {
                $category = self::$categoryCache[$categoryId];
            } else {
                $category = $folder->category;
                self::$categoryCache[$categoryId] = $category;
            }
        }

        return [
            'id' => (string) $this->id,
            'attributes' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'folder' => $folder ?? ['name' => 'Unknown'],
                'metadata' => $metadata,
                'category' => $category,
                'video_id' => $videoId,
                'video_name' => $metadata->title ?? $this->name ?? 'Deleted',
                'file_name' => $this->name,
            ],
        ];
    }
}

// 'user' => $this->user,
// 'video' => $video,
// 'metadata' => $metadata ? new MetadataResource($metadata) : null,
// 'category' => $category ? new CategoryResource($category) : null,

// 'user' => $this->user,
// 'video' => $this->video,
// 'user_id' => (string)$this->user->id,
// 'user_name' => $this->user->name,
// 'video_id' => $this->video ? (string)$this->video->id : ($this->metadata && $this->metadata->video ? $this->metadata->video->id : null),
// 'video_name' => $this->metadata ? $this->metadata->title : ($this->video ? ($this->video->title ?? $this->video->name) : $this->name) ?? 'Deleted',
// 'file_name' => $this->video ? $this->video->name : $this->name,
// 'folder' => $this->video ? new FolderResource($this->video->folder) : array("name" => ($this->metadata && $this->metadata->video ? $this->metadata->video->folder->name : 'Unknown')),
// 'category' => $this->video && $this->video->folder->category ? new CategoryResource($this->video->folder->category) : null,
// 'metadata_id' => $this->metadata ? $this->metadata->id : 'None',
// 'metadata' => $this->metadata ? new MetadataResource($this->metadata) : null,

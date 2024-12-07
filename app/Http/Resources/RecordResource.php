<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource {

    // Mega Yikes AI code
    protected static $categoryCache = [];
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $metadata = $this->metadata;
        $video = $metadata ? $metadata->video : $this->video; // $this->video is only for compatibility with old database design, new records dont need to use it
        $folder = $video ? $video->folder : null;
        $category = $category = null;

        if ($folder) {
            $categoryId = $folder->category_id;
            if (isset(self::$categoryCache[$categoryId])) {
                $category = self::$categoryCache[$categoryId];
            } else {
                $category = $folder->category;
                self::$categoryCache[$categoryId] = $category;
            }
        }

        return [
            'id' => (string)$this->id,
            'attributes' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'folder' => $folder ?? array("name" => 'Unknown'),
                'metadata' => $metadata,
                'category' => $category,
                'video_id' => $this->video_id,
                'video_name' => $metadata->title ?? $this->name ?? 'Deleted',
                'file_name' => $video ? $video->name : $this->name,
            ]
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

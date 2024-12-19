<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'file_count' => $this->videos_count ?? 0, //$videos->count(),
            'total_size' => $this->total_size,
            'category_id' => $this->category_id,
            'videos' => $this->when($request->videos, function () {
                return VideoResource::collection($this->videos);
            }),
            'series' => new SeriesResource($this->series),
        ];
    }
}

/*
Mega Yikes AI code
protected static $categoryCache = [];

$videos = $request->videos ? $this->videos : [];

$totalFileSize = $this->videos->sum(function ($video) {
return $video->metadata ? $video->metadata->file_size : 0;
});
$self = Folder::with('videos.metadata')->find($this->id);

$categoryId = $this->category_id; // Assuming editor_id is the foreign key
$category = null;

if (isset(self::$categoryCache[$categoryId])) {
    $category = self::$categoryCache[$categoryId];
} else {
    $category = $this->category;
    self::$categoryCache[$categoryId] = $category;
}

'total_size' => $this->videos->sum(function ($video) {
    return $video->metadata ? $video->metadata->file_size : 0;
}),
*/

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class CategoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {

        $folders = $this->whenLoaded('folders');

        $videosCount = $folders->sum(function ($folder) {
            return $folder->video_count ?? $folder->series->episodes;
        });

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'default_folder_id' => $this->default_folder_id,
            'folders_count' => count($folders) ?? 0,
            'folders' => FolderResource::collection($folders),
            'videos_count' => $videosCount ?? 0,
            'created_at' => $this->created_at,
            'last_scan' => $this->last_scan,
        ];
    }
}

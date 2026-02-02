<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        if (! $this->relationLoaded('folders')) {
            $this->loadMissing('folders');
        }

        $folders = $this->folders;

        $videosCount = $folders->sum(function ($folder) {
            return $folder->series->episodes;
        });

        $totalSize = $folders->sum(function ($folder) {
            return $folder->series->total_size;
        });

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'default_folder_id' => $this->default_folder_id,
            'folders' => FolderResource::collection($folders),
            'folders_count' => count($folders) ?? 0,
            'videos_count' => $videosCount ?? 0,
            'total_size' => $totalSize ?? 0,
            'created_at' => $this->created_at,
            'last_scan' => $this->last_scan,
            'is_private' => Auth::user() && Auth::id() === 1 ? $this->is_private : false,
        ];
    }
}

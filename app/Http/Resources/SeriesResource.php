<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesResource extends JsonResource {

    protected static $editorCache = [];
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $editorId = $this->editor_id; // Assuming editor_id is the foreign key
        $editor = null;

        if (isset(self::$editorCache[$editorId])) {
            $editor = self::$editorCache[$editorId];
        } else {
            $editor = $this->editor;
            self::$editorCache[$editorId] = $editor;
        }
        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            // 'editor' => $editor,
            'editor_id' => $this->editor_id,
            'title' => $this->title ?? $this->folder->name,
            'description' => $this->description,
            'studio' => $this->studio,
            'rating' => $this->rating,
            'seasons' => $this->seasons,
            'episodes' => $this->episodes,
            'films' => $this->films,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'thumbnail_url' => $this->thumbnail_url,
            'date_updated' => $this->updated_at,
        ];
    }
}

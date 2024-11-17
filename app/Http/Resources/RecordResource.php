<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->name,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'user_id' => (string)$this->user->id,
                'user_name' => $this->user->name,
                'video_id' => $this->video ? (string)$this->video->id : null,
                'video_name' => $this->video ? ($this->video->title ?? $this->video->name) : $this->name,
                'file_name' => $this->video ? $this->video->name : $this->name,
                'folder_id' => $this->video ? $this->video->folder_id : null,
                'folder_name' => $this->video ? $this->video->folder->name : 'Deleted',
                'category_name' => $this->video ? $this->video->folder->category->name : null
            ]
        ];
    }
}

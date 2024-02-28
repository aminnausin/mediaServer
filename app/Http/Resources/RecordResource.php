<?php

namespace App\Http\Resources;

use App\Models\Folder;
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
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'user_id' => (string)$this->user->id,
                'user name' => $this->user->name,
                'user email' => $this->user->email,
                'video_id' => (string)$this->video->id,
                'video name' => $this->video->name,
                'folder id' => $this->video->folder_id,
                'folder name' => $this->video->folder->name
            ]
        ];
    }
}

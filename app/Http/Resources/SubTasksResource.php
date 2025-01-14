<?php

namespace App\Http\Resources;

use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubTasksResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'status_key' => $this->status,
            'status' => TaskStatus::getLabel($this->status),
            'name' => $this->name,
            'summary' => $this->summary,
            'progress' => $this->progress,
            'duration' => $this->duration,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
        ];
    }
}

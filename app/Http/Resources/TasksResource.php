<?php

namespace App\Http\Resources;

use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource {
    protected static $userCache = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array {
        $userId = $this->user_id; // Assuming editor_id is the foreign key
        $user = null;

        if (isset(self::$userCache[$userId])) {
            $user = self::$userCache[$userId];
        } else {
            $user = $this->user;
            self::$userCache[$userId] = $user;
        }

        return [
            'id' => $this->id,
            'user' => $user->name,
            'status_key' => $this->status,
            'status' => TaskStatus::getLabel($this->status),
            'name' => $this->name,
            'description' => $this->description,
            'summary' => $this->summary,
            'sub_tasks' =>  [],
            'sub_tasks_total' => $this->sub_tasks_total,
            'sub_tasks_pending' => $this->sub_tasks_pending,
            'sub_tasks_complete' => $this->sub_tasks_complete,
            'sub_tasks_failed' => $this->sub_tasks_failed,
            'duration' => $this->duration,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
        ];
    }
}

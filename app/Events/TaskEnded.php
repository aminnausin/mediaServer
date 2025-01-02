<?php

namespace App\Events;

use App\Http\Resources\TasksResource;
use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskEnded implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TasksResource $resource;

    /**
     * Create a new event instance.
     */
    public function __construct(public Task $task) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        try {
            $this->resource = new TasksResource($this->task);

            // code...
            return [
                new PrivateChannel("tasks.{$this->task->user_id}"),
            ];
        } catch (\Throwable $th) {
            dump($th->getMessage());
        }
    }
}

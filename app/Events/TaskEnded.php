<?php

namespace App\Events;

use App\Enums\TaskStatus;
use App\Http\Resources\TasksResource;
use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use function Illuminate\Log\log;

class TaskEnded implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;


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

            // code...
            return [
                new PrivateChannel("tasks.{$this->task->id}"),
                new PrivateChannel("dashboard.tasks"),
            ];
        } catch (\Throwable $th) {
            dump($th->getMessage());
        }
    }

    /**
     *
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array {
        return ['task' => new TasksResource($this->task)];
    }
}

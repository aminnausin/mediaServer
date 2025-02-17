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
use Illuminate\Support\Facades\Log;

// This notifys the person who initiated the task that it is finished.
class TaskEnded implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue on which to place the broadcasting job.
     *
     * @var string
     */
    public $queue = 'high';

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
            return [
                new PrivateChannel("tasks.{$this->task->id}"),
            ];
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error('Unable to broadcast task ended update', ['error' => $th->getMessage()]);
            throw $th;
        }
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array {
        try {
            return ['task' => new TasksResource($this->task)];
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error('Unable to broadcast task ended update', ['error' => $th->getMessage()]);
            throw $th;
        }
    }
}

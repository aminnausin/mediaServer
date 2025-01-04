<?php

namespace App\Events;

use App\Http\Resources\SubTasksResource;
use App\Models\SubTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubTaskUpdated implements ShouldBroadcast {
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
    public function __construct(public SubTask $subTask) {
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
                new PrivateChannel("tasks.{$this->subTask->task_id}.subtasks"),
            ];
        } catch (\Throwable $th) {
            dump($th->getMessage());
        }
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array {
        return ['subTask' => new SubTasksResource($this->subTask)];
    }
}

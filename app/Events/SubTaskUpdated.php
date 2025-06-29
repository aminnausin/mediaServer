<?php

namespace App\Events;

use App\Http\Resources\SubTasksResource;
use App\Models\SubTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
    public function __construct(public int $subTaskId, public int $taskId) {
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
                new PrivateChannel("tasks.{$this->taskId}.subtasks"),
            ];
        } catch (\Throwable $th) {
            Log::error('Unable to broadcast subtask update', ['error' => $th->getMessage()]);
            throw $th;
        }
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array {
        try {
            $subTask = SubTask::findOrFail($this->subTaskId);

            return ['subTask' => new SubTasksResource($subTask)];
        } catch (\Throwable $th) {
            Log::error('Unable to broadcast subtask update', ['error' => $th->getMessage()]);
            throw $th;
        }
    }
}

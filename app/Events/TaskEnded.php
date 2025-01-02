<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
        // $task = Task::where('id', $this->taskId)->first();
        // dump("hhhhh{$this->task->userId}");
        try {
            //code...
            return [
                new PrivateChannel("tasks.{$this->task->userId}"),
                new Channel('dashboard'),
            ];
        } catch (\Throwable $th) {
            //throw $th;
            dump($th->getMessage());
        }
    }
}

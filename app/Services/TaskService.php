<?php

namespace App\Services;

use App\Events\SubTaskUpdated;
use App\Events\TaskEnded;
use App\Events\TaskUpdated;
use App\Models\SubTask;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService {
    public function createTask(array $attrs): Task {
        $task = Task::create($attrs);

        try {
            TaskUpdated::dispatch($task->id);
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error('Unable to broadcast task creation', ['error' => $th->getMessage()]);
        }

        return $task;
    }

    public function createSubTask(array $attrs): SubTask {
        return SubTask::create($attrs);
    }

    /**
     * Update Task by ID.
     */
    public function updateTask(int $taskId, array $attr, bool $taskEnded = false): ?Task {
        DB::table('tasks')->where('id', $taskId)->update($attr);

        if ($taskEnded) {
            TaskEnded::dispatch($taskId);
        } else {
            TaskUpdated::dispatch($taskId);
        }

        return Task::findOrFail($taskId);
    }

    /**
     * Update SubTask by ID.
     */
    public function updateSubTask(int $subTaskId, array $attr, bool $broadcast = false): ?SubTask {
        $subTask = SubTask::find($subTaskId);

        if (! $subTask) {
            return null;
        }

        foreach ($attr as $key => $value) {
            $subTask->{$key} = $value;
        }
        $subTask->save();
        if ($broadcast) {
            try {
                SubTaskUpdated::dispatch($subTask->id, $subTask->task->id);
            } catch (\Throwable $th) {
                dump($th->getMessage());
                Log::error('Unable to broadcast subTask update', ['error' => $th->getMessage()]);
            }
        }

        return $subTask;
    }

    public function updateTaskCounts(int $taskId, array $attr, bool $broadcast = true): ?Task {
        $table = DB::table('tasks');
        $updated = false;

        foreach ($attr as $key => $value) {
            if ($value === '++') {
                $updated |= $table->where('id', $taskId)->increment($key);
            } elseif ($value === '--') {
                $updated |= $table->where('id', $taskId)->decrement($key);
            } elseif (is_numeric($value)) {
                $updated |= $table->where('id', $taskId)->increment($key, (int) $value);
            }
        }

        if (! $updated) {
            return null;
        }

        if ($broadcast) {
            TaskUpdated::dispatch($taskId);
        }

        return Task::findOrFail($taskId);
    }
}

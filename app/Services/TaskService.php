<?php

namespace App\Services;

use App\Events\SubTaskUpdated;
use App\Events\TaskEnded;
use App\Events\TaskUpdated;
use App\Models\SubTask;
use App\Models\Task;
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
        $task = Task::find($taskId);

        if (! $task) {
            return null;
        }

        foreach ($attr as $key => $value) {
            $task->{$key} = $value;
        }
        $task->save();

        try {
            if ($taskEnded) {
                TaskEnded::dispatch($task->id);
            } else {
                TaskUpdated::dispatch($task->id);
            }
        } catch (\Throwable $th) {
            dump($th->getMessage());
            Log::error('Unable to broadcast task update', ['error' => $th->getMessage()]);
        }

        return $task;
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
        $task = Task::find($taskId);

        if (! $task) {
            return null;
        }

        foreach ($attr as $key => $value) {
            if ($value === '++') {
                $task->{$key}++;
            } elseif ($value === '--') {
                $task->{$key}--;
            } elseif (is_numeric($value)) {
                $task->{$key} += (int) $value;
            }
        }
        $task->save();
        if ($broadcast) {
            try {
                TaskUpdated::dispatch($task->id);
            } catch (\Throwable $th) {
                dump($th->getMessage());
                Log::error('Unable to broadcast task count update', ['error' => $th->getMessage()]);
            }
        }

        return $task;
    }
}

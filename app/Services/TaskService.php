<?php

namespace App\Services;

use App\Events\SubTaskUpdated;
use App\Events\TaskUpdated;
use App\Models\SubTask;
use App\Models\Task;

class TaskService {
    public function createTask(array $attrs): Task {
        $task = Task::create($attrs);
        broadcast(new TaskUpdated($task));

        return $task;
    }

    public function createSubTask(array $attrs): SubTask {
        $subTask = SubTask::create($attrs);
        // broadcast(new SubTaskUpdated($subTask));

        return $subTask;
    }

    /**
     * Update Task by ID.
     */
    public function updateTask(int $taskId, array $attr): ?Task {
        $task = Task::find($taskId);

        if (! $task) {
            return null;
        }

        foreach ($attr as $key => $value) {
            $task->{$key} = $value;
        }
        $task->save();

        broadcast(new TaskUpdated($task));

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
        // if ($broadcast === true) broadcast(new SubTaskUpdated($subTask));

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
            } else {
                $task->{$key}--;
            }
        }
        $task->save();
        if ($broadcast === true) {
            broadcast(new TaskUpdated($task));
        }

        return $task;
    }
}

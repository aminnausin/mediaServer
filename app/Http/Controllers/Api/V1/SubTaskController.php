<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubTasksResource;
use App\Models\SubTask;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubTaskController extends Controller {
    use HttpResponses;

    /**
     * Display the specified resource.
     */
    public function show(Task $task) {
        return SubTasksResource::collection(
            SubTask::where('task_id', $task->id)->orderBy('id')->get()
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubTask $subTask) {
        $taskId = $subTask->task_id;
        $subTaskId = $subTask->id;
        $subTask->delete();

        Log::info('Subtask deleted', [
            'subtask_id' => $subTaskId,
            'task_id' => $taskId,
            'user_id' => Auth::id(),
        ]);

        return response()->noContent();
    }
}

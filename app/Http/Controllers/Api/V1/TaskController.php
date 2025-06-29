<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TasksResource;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        if ($res = $this->isNotAuthorised()) {
            return $res;
        }

        return TasksResource::collection(
            Task::all()->sortByDesc('created_at')
        );
    }

    public function stats(Request $request) {
        try {
            $failValue = TaskStatus::FAILED->value;
            $cancelValue = TaskStatus::CANCELLED->value;

            $results = DB::table('sub_tasks')
                ->selectRaw('AVG(duration) as avg_duration')
                ->selectRaw('COUNT(*) as count_subtasks')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0) as avg_fail_rate', [$failValue])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as count_cancelled', [$cancelValue])
                ->first();

            $taskCounts = Task::select(
                DB::raw('COUNT(*) as count_tasks'),
                DB::raw('AVG(sub_tasks_total) as avg_count_sub_tasks')
            )->first();

            $currentCounts = [
                'avg_duration' => $results->avg_duration,
                'avg_fail_rate' => $results->avg_fail_rate,
                'count_cancelled' => $results->count_cancelled,
                'avg_count_sub_tasks' => $taskCounts->avg_count_sub_tasks,
                'count_tasks' => $taskCounts->count_tasks,
                'count_running' => Task::where('status', TaskStatus::PROCESSING)->count(),
                'count_subtasks' => $results->count_subtasks,
            ];

            return response()->json($currentCounts);
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get stats. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task) {
        if ($res = $this->isNotAuthorised()) {
            return $res;
        }

        $task->delete();

        return response()->noContent();
    }

    public function cancel(Task $task, TaskService $taskService) {
        $batch = $task->batch_id ? Bus::findBatch($task->batch_id) : null;

        if (! $batch) {
            $taskService->updateTask($task->id, ['status' => TaskStatus::INCOMPLETE]);

            $message = $task->batch_id
                ? 'Batch not found.'
                : 'No batch ID associated with this task.';

            $status = $task->batch_id ? 404 : 422;

            return response()->json(['message' => $message], $status);
        }

        $batch->cancel();
        $taskService->updateTask($task->id, ['status' => TaskStatus::CANCELLED]);

        return response()->json(['message' => 'Batch canceled successfully.']);
    }

    private function isNotAuthorised() {
        if (Auth::id() != 1) {
            $this->forbidden('Unauthorised request.');
        }

        return null;
    }
}

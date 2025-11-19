<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TasksResource;
use App\Models\SubTask;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
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
            Task::with('user')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function waitTimes(Request $request) {
        return response()->json(Cache::remember('wait_times', 300, function () {
            return [
                'sync' => $this->latestDuration(SubTask::class, 'Sync Files'),
                'index' => $this->latestDuration(Task::class, 'Index Files'),
                'scan' => $this->latestDuration(Task::class, 'Scan Files'),
                'verify_files' => $this->latestDuration(Task::class, 'Verify Files'),
                'verify_folders' => $this->latestDuration(Task::class, 'Verify Folders'),
                'embed_uid' => $this->latestDuration(SubTask::class, 'Embed UID in video file %', true),
            ];
        }));
    }

    public function stats(Request $request) {
        return response()->json(Cache::flexible('task_stats', [15, 60], function () {
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

            return [
                'avg_duration' => $results->avg_duration,
                'avg_fail_rate' => $results->avg_fail_rate,
                'count_cancelled' => $results->count_cancelled,
                'avg_count_sub_tasks' => $taskCounts->avg_count_sub_tasks,
                'count_tasks' => $taskCounts->count_tasks,
                'count_running' => Task::where('status', TaskStatus::PROCESSING)->count(),
                'count_subtasks' => $results->count_subtasks,
            ];
        }));
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

    private function latestDuration($model, $name, $like = false, $limit = 5) {
        $query = $model::where('status', TaskStatus::COMPLETED);
        $query = $like
            ? $query->where('name', 'like', $name)
            : $query->where('name', '=', $name);

        return $query->latest()->limit($limit)->pluck('duration')->avg();
    }
}

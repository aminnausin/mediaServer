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
        if (! Auth::user() || Auth::user()->id !== 1) {
            abort(403, 'Not allowed access to tasks.');
        }

        try {
            return
                TasksResource::collection(
                    Task::all()->sortByDesc('created_at')
                );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get list of tasks. Error: ' . $th->getMessage(), 500);
        }
    }

    public function stats(Request $request) {
        // $this->period = $request->query('period', '1_hour');
        // $interval = $this->periodAsInterval();
        // $startDate = today()->sub($interval);

        try {
            if (! Auth::user()->id == 1) {
                return Response('Forbidden', 403);
            }

            $results = DB::table('sub_tasks')
                ->select(
                    DB::raw('AVG(duration) as avg_duration'),
                    // DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) / COUNT(*) * 100 as avg_fail_rate'),
                    // DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as count_cancelled'),
                    DB::raw('COUNT(*) as count_subtasks')
                )
                ->first();

            $taskCounts = Task::select(
                DB::raw('COUNT(*) as count_tasks'),
                DB::raw('AVG(sub_tasks_total) as avg_count_sub_tasks')
            )->first();

            $currentCounts = [
                'avg_duration' => $results->avg_duration,
                'avg_fail_rate' => 0,
                'count_cancelled' => 0,
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
        return $this->isNotAuthorised() ?? $task->delete() ? $this->success('', 'Success', 200) : $this->error('', 'Not found', 404);
    }

    public function cancel(Task $task, TaskService $taskService) {
        if ($task->batch_id) {
            $batch = Bus::findBatch($task->batch_id);
            if ($batch) {
                $batch->cancel();
                $taskService->updateTask($task->id, ['status' => TaskStatus::CANCELLED]);

                return response()->json(['message' => 'Batch canceled successfully.']);
            } else {
                $taskService->updateTask($task->id, ['status' => TaskStatus::INCOMPLETE]);

                return response()->json(['message' => 'Batch not found.'], 404);
            }
        }
        $taskService->updateTask($task->id, ['status' => TaskStatus::INCOMPLETE]);

        return response()->json(['message' => 'No batch ID associated with this task.'], 400);
    }

    private function isNotAuthorised() {
        if (Auth::id() != 1) {
            return $this->error('', 'Unauthorised request.', 403);
        }

        return null;
    }
}

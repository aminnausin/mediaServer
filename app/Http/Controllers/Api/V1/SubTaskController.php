<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubTasksResource;
use App\Models\SubTask;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubTaskController extends Controller {
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task) {
        if (Auth::id() !== 1) {
            return $this->forbidden();
        }

        try {
            return
                SubTasksResource::collection(
                    SubTask::where('task_id', $task->id)->get()->sortBy('id')
                );
        } catch (\Throwable $th) {
            return $this->error(null, 'Unable to get related subtasks. Error: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubTask $subTask) {
        return $this->isNotAuthorised() ?? $subTask->delete() ? $this->success('', 'Success', 200) : $this->error($subTask, 'Not found', 404);
    }

    private function isNotAuthorised() {
        if (Auth::id() != 1) {
            return $this->error('', 'Unauthorised request.', 403);
        }

        return null;
    }
}

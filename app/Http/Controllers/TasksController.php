<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TasksResource;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TasksResource::collection(
            Task::where('user_id', Auth::user()->id)->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $request->validated($request->all());

        $task = Task::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority
        ]);

        return new TasksResource($task);
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return $this->isNotAuthorised($task) ?? new TasksResource($task);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        if(Auth::user()->id != $task->user_id){
            return $this->error('', 'Unauthorised request.', 403);
        }

        $task->update($request->all());

        return new TasksResource($task);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        return $this->isNotAuthorised($task) ?? $task->delete();
    }

    private function isNotAuthorised(Task $task)
    {
        if(Auth::user()->id != $task->user_id){
            return $this->error('', 'Unauthorised request.', 403);
        }
        return null;
    }
}

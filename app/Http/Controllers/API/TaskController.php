<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\Board;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\TaskResource as TaskResource;
use Auth;
use Validator;
use Illuminate\Support\Str;

class TaskController extends BaseController
{
    public function index()
    {
        $task = Task::where('user_id',Auth::user()->id)->get();
        // if (count($task) == 0) {
        //    return $this->sendError('Task not found');
        // }
        return $this->sendResponse(TaskResource::collection($task), 'Board has been retrieved successfully!');
    }

    
    public function store(Request $request, Board $board)
    {
        $input = $request->all();
        // return response()->json($request->image, 200);
        
        $validator = Validator::make($input,[
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png,gif',
            'due_date' => 'nullable',
            'current_status' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        if ($request->image != null) {
            $photo = $request->image;
            $newPhoto = Str::random() . time() . $photo->getClientOriginalExtension();
            $photo->move('task/image',$newPhoto);

            $input['image']='task/image/'.$newPhoto;
        }

        $input['user_id'] = Auth::user()->id;
        $input['board_id'] = $board->id;
        $task = Task::create($input);

        return $this->sendResponse(new TaskResource($task), 'The Task has been created successfully!');
    }

   
    public function show(Board $board)
    {
        $errorMessage = [];

        if ( Auth::user()->board->find($task->board_id) == null) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }
        
        return $this->sendResponse(['board' => new BoardResource($board),
            'task' => TaskResource::collection($task),
            'task' => TaskResource::collection($task)], 'The board has been retrieved successfully!');
    }

   
    public function update(Request $request, Task $task)
    {
        $errorMessage = [];

        if ( Auth::user()->board->find($task->board_id) == null) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }

        $input = $request->all();
        
        $validator = Validator::make($input,[
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png,gif',
            'due_date' => 'nullable',
            'current_status' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        if ($request->image != null) {
            $photo = $request->image;
            $newPhoto = Str::random() . time() . $photo->getClientOriginalExtension();
            $photo->move('task/image',$newPhoto);

            $input['image']='task/image/'.$newPhoto;
            $task->image = $input['image'];
        }
        
        if ($request->due_date) {
            $task->due_date = $input['due_date'];
        }

        if ($request->current_status) {
            $task->current_status = $input['current_status'];
        }

        $task->title = $input['title'];
        $task->description = $input['description'];
        
        $task->save();

        return $this->sendResponse(new TaskResource($task), 'The Task has been updated successfully!');

    }

   
    public function destroy(Task $task)
    {
        $errorMessage = [];
        // return response()->json($board, 200);
        // return response()->json(Auth::user()->board->find($task->board_id), 200);

        if (Auth::user()->board->find($task->board_id) == null) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }

        $task->delete();
        
        return $this->sendResponse(new TaskResource($task), 'The Task has been deleted successfully!');

    }
}

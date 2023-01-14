<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use App\Models\Board;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\TaskResource as TaskResource;
use App\Models\Label;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TaskController extends BaseController
{
    public function index()
    {
        $task = Task::where('user_id',Auth::user()->id)->get();
        
        return $this->sendResponse(TaskResource::collection($task), 'Board has been retrieved successfully!');
    }

    
    public function store(Request $request, Board $board)
    {
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
        }

        $input['board_id'] = $board->id;
        $task = Task::create($input);

        return $this->sendResponse(new TaskResource($task), 'The Task has been created successfully!');
    }

   
    public function show(Request $request,Board $board)
    {
        $sort_search =null;
        $sort_title = null;
        $sort_created = null;
        $errorMessage = [];
        
        if (Auth::id() != $board->user_id) {
            return $this->sendError('unauthorized to make this operation' ,$errorMessage);
        }

        $task = $board->task();
// return response()->json($task, 200);
        if ($request->has('sort_title')) {
            $sort_title = $request->sort_title;
            $task->orderBy('title',$sort_title);
        }

        if ($request->has('sort_created')) {
            $sort_created = $request->sort_created;
            $task->orderBy('created_at',$sort_created);
        }

        if ($request->has('search')){
            $sort_search = $request->search;
            $task = $task->where('title', 'like', '%'.$sort_search.'%');
        }

                
        if ($request->has('filter_label')){
            $label_id = Label::where('title', 'like', '%'.$request->filter_label.'%')->get('task_id');
            $task = $task->whereIn('id',$label_id);
        }


        $task = $task->get();

        return $this->sendResponse(TaskResource::collection($task),'The Task of the owner has been retrieved successfully!');
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

        if (Auth::user()->board->find($task->board_id) == null) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }

        $task->delete();
        
        return $this->sendResponse(new TaskResource($task), 'The Task has been deleted successfully!');

    }
}

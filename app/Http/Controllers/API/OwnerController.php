<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Board;
use App\Models\Label;
use App\Models\Owner;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerController extends BaseController
{
    

    public function assignTask(Request $request, Task $task)
    {
         $request->validate([
            'assign_id' => 'required'
        ]);

        $errorMessage = [];
        
        if (Auth::id() != $task->board->user_id) {
            return $this->sendError('unauthorized to make this operation' ,$errorMessage);
        }

        if (User::where('id',$request->assign_id)->where('type','developer')->first()
             && !($task->current_status == "to-do" || $task->current_status == "in-prograss" )) {    
                return $this->sendError('It cannot be assigned to this developer. The current status: '. $task->current_status, $errorMessage);
        }
        elseif (User::where('id',$request->assign_id)->where('type','tester')->first()
         && !($task->current_status == "testing")) {
            return $this->sendError('It cannot be assigned to this Tester. The current status: '. $task->current_status, $errorMessage);
        }
        elseif (User::where('id',$request->assign_id)->where('type','owner')->first()) {
            return $this->sendError('It cannot be assigned to this user because he is owner ', $errorMessage); 
        }


        $task->user_id = $request->assign_id;
        $task->save();
        
        $user = User::where('id', $request->assign_id)->first();
        
        return $this->sendResponse(['Task' => new TaskResource($task),'User' => new UserResource($user)],'The assign has been selected successfully!');
    }


    public function showAllTask(Request $request)
    {
        $sort_created =null;
        $filter_label = null;
        $sort_title =null;
        $sort_search =null;
        // $brands = Brand::orderBy('name', 'asc');

        $board_id = Auth::user()->board->pluck('id');
        $task = Task::whereIn('board_id',$board_id);
        
        if ($request->has('sort_title')) {
            $sort_title = $request->sort_title;
            $task = $task->orderBy('title',$sort_title);
        }

        if ($request->has('sort_created')) {
            $sort_created = $request->sort_created;
            $task = $task->orderBy('created_at',$sort_created);
        }

        if ($request->has('search')){
            $sort_search = $request->search;
            $task = $task->where('title', 'like', '%'.$sort_search.'%');
        }

        if ($request->has('filter_label')){
            $filter_label = $request->filter_label;
            $label_id = Label::where('title', 'like', '%'.$filter_label.'%')->get('task_id');
            $task = $task->whereIn('id',$label_id);
        }

        $task = $task->get(); 

        return $this->sendResponse(TaskResource::collection($task),'The Task of the owner has been retrieved successfully!');
    }


    public function changeTaskStatus(Request $request, Task $task)
    {
        $errorMessage = [];
        
        if (Auth::id() != $task->board->user_id) {
            return $this->sendError('unauthorized to make this operation' ,$errorMessage);
        }

        $input = $request->all();
        $validator = Validator::make($request->all(),[
            'change_status'=> 'required'
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        // if the change status is same the current status
        if ($task->current_status == $request->change_status) {
            return $this->sendError('The current status value is same the change status!',$errorMessage);        
        }
        //if the change status is unauthrized to this user
        if (!($request->change_status == 'to-do' || $request->change_status == 'in-progress' || $request->change_status == 'testing'
            || $request->change_status == 'dev-review' || $request->change_status == 'done' || $request->change_status == 'close')) {

            return $this->sendError('There is no changes status like that ', $errorMessage);
        }

        //add the record to the change status tables
        $input['user_name'] = Auth::user()->name;
        $input['detail'] = 'Change status from '. $task->current_status . ' to ' . $input['change_status'] . '.';  
        $input['task_id'] = $task->id;
        
        $status = Status::create($input);

        $task->current_status = $request->change_status;
        $task->save();

        return $this->sendResponse(['task' => new TaskResource($task), 'status_changes' => new StatusResource($status)], 'current status have been changed successfully!');    

        
    }

    public function ShowAllTaskLogs()
    {
        $board_id = Auth::user()->board->pluck('id');
        $task_id = Task::whereIn('board_id',$board_id)->get('id');
        $logs = Status::whereIn('task_id',$task_id)->get();
        
        return $this->sendResponse(['logs' => StatusResource::collection($logs)],'The Task of the owner has been retrieved successfully!');
    }
    
    public function ShowTaskLogs(Task $task)
    {
        $errorMessage = [];

        if (Auth::id() != $task->board->user_id) {
            return $this->sendError('unauthorized to make this operation' ,$errorMessage);
        }
        $logs = $task->status;

        return $this->sendResponse(['logs' => StatusResource::collection($logs)],'The Task of the owner has been retrieved successfully!');
    }
}

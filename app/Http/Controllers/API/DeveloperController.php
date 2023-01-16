<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Models\Developer;
use App\Models\Label;
use App\Models\Status;
use App\Models\Task;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperController extends BaseController
{
    
    public function showAllTask(Request $request)
    {   
        // the value of $sort_title and $sort_created should be desc or asc
        $sort_title = null;
        $sort_created = null;
        $sort_search = null;
        $filter_label = null;
        $task = Task::where('user_id',Auth::id());
        
        if ($request->has('sort_title')) {
            $sort_title = $request->sort_title;
            $task->orderBy('title',$sort_title);
        }
        if ($request->has('sort_created')) {
            $sort_created = $request->sort_created;
            $task->orderBy('created_at', $sort_created);
            dd($task->get());
        }

        if ($request->has('search')){
            $sort_search = $request->search;
            $task = $task->where('title', 'like', '%'.$sort_search.'%');
        }

        if ($request->has('filter_label')){
            $filter_label = $request->filter_label;
            $task_id = Label::where('title', 'like', '%'.$filter_label.'%')->get('task_id');
            $task = $task->whereIn('id',$task_id);
        }

        $task = $task->get();

        return $this->sendResponse(TaskResource::collection($task), 'Task retrieved successfully for the developer!');
    }

   
    public function changeTaskStatus(Request $request, Task $task)
    {
        $errorMessage = [];

        // first checke if the task is assigned to this user
        if ($task->user_id != Auth::id()) {
            return $this->sendError('unauthorized to do this opratoin', $errorMessage);
        }

        $input = $request->all();
        $validator = Validator::make($request->all(),[
            'change_status'=> 'required'
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        if (($task->current_status == 'to-do' && $request->change_status == 'in-progress') 
            || ($task->current_status == 'in-progress' && $request->change_status == 'testing')) {
        
            //add the record to the change status tables
            $input['user_name'] = Auth::user()->name;
            $input['detail'] = 'Change status from '. $task->current_status . ' to ' . $input['change_status'] . '.';  
            $input['task_id'] = $task->id;
            
            $status = Status::create($input);

            $task->current_status = $request->change_status;
            $task->save();

            return $this->sendResponse(['task' => new TaskResource($task), 'status_changes' => new StatusResource($status)], 'current status have been changed successfully!');    

        }
        // if the change status is same the current status
        elseif ($task->current_status == $request->change_status) {
            return $this->sendError('The current status value is same the change status!',$errorMessage);        
        }
        //if the change status is unauthrized to this user
        elseif ($request->change_status == 'close' || $request->change_status == 'done' || $request->change_status == 'to-do' 
                || ($task->current_status == 'to-do' && $request->change_status == 'testing')
                || ($task->current_status == 'in-progress' && $request->change_status == 'to-do')) {
            return $this->sendError('unauthorized to change ', $errorMessage);
        }else{
            return $this->sendError('There is no changes status like that ', $errorMessage);
        }
        
    }

}

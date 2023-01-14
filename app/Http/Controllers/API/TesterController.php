<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\StatusResource;
use App\Http\Resources\TaskResource;
use App\Models\Status;
use App\Models\Task;
use App\Models\Tester;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TesterController extends BaseController
{
    public function showAllTask(Request $request)
    {
        $sort_search = null;
        $sort_created = null;
        $sort_title = null;

        $task = Task::where('user_id',Auth::id());

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
        $task = $task->get();
        // else{
        //     $task->get();
        // }

        return $this->sendResponse(TaskResource::collection($task), 'Task retrieved successfully for the Tester!');
    }


    public function changeTaskStatus(Request $request, Task $task)
    {
        $errorMessage = [];

        // first checke if the task is assigned to this user
        if ($task->user_id != Auth::id()) {
            return $this->sendError('unauthorized to do this operatoin', $errorMessage);
        }

        $input = $request->all();
        $validator = Validator::make($request->all(),[
            'change_status'=> 'required'
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        if ($task->current_status == 'testing' && $request->change_status == 'dev-review') {
        
            
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
        elseif ($request->change_status == 'to-do' || $request->change_status == 'in-progress'
                  || $request->change_status == 'close' || $request->change_status == 'done'
                  || ($request->change_status == 'testing' && $task->current_status == 'dev-review')) {

            return $this->sendError('unauthorized to change ', $errorMessage);
        
        }else{  
        
            return $this->sendError('There is no changes status like that ', $errorMessage);
        
        }
        
    }


}

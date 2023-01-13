<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Board;
use App\Models\Owner;
use App\Models\Task;
use App\Models\User;
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

    public function showAllTask()
    {
        $board_id = Auth::user()->board->pluck('id');
        $task = Task::whereIn('board_id',$board_id)->get();
        
        return $this->sendResponse(TaskResource::collection($task),'The Task of the owner has been retrieved successfully!');
    }

    
}

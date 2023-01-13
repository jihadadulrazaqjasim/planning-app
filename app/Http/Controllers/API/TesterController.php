<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Tester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TesterController extends BaseController
{
    public function showAllTask()
    {
        $task = Task::where('user_id',Auth::id())->get();
        return $this->sendResponse(TaskResource::collection($task), 'Task retrieved successfully for the Tester!');
    }


    public function changeTaskStatus(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(),[
            'change_status'=> 'required'
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }
        $errorMessage = [];

        if (($task->current_status == 'to-do' && $request->change_status == 'in-progress') 
            || ($task->current_status == 'in-progress' && $request->change_status == 'testing')) {
        
            $task->current_status = $request->change_status;
                //add the record to the change status tables

            return $this->sendResponse(new TaskResource($task), 'current status have been changed successfully!');    

        }
        elseif ($request->change_status == 'close' || $request->change_status == 'done' || $request->change_status == 'to-do') {
            return $this->sendError('unauthorized to change ', $errorMessage);
        }else{
            return $this->sendError('Thir is no current status like that ', $errorMessage);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tester  $tester
     * @return \Illuminate\Http\Response
     */
    public function edit(Tester $tester)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tester  $tester
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tester $tester)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tester  $tester
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tester $tester)
    {
        //
    }
}

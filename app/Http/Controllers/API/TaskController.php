<?php

namespace App\Http\Controllers\API;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\BoardResource as BoardResource;
use Auth;
use Validator;

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
        
        $validator = Validator::make($input,[
            'title' => 'required|max:255',
            'description' => 'required',
            'image' => 'nullable',
            'due_date' => 'nullable',
            'current_status' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        $input['user_id'] = Auth::user()->id;
        $input['board_id'] = $board->id;
        $task = Task::create($input);

        return $this->sendResponse(new TaskResource($task), 'The Task has been created successfully!');
    }

   
    public function show($id)
    {
        $board = Board::where('id',$id)->first();
        
        $errorMessage = [];
        
        if ($board === null) {
            $this->sendError('The board not found', $errorMessage);
        }

        $task = $board->task();
        $lable = $board->task()->lable();


        return $this->sendResponse(['board' => new BoardResource($board),
            'task' => TaskResource::collection($task),
            'task' => TaskResource::collection($task)], 'The board has been retrieved successfully!');
    }

   
    public function update(Request $request, Board $board)
    {
        $errorMessage = [];

        if ($board->id != Auth::user()->id) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }

        $input = $request->all();
        
        $validator = Validator::make($input,[
            'title' => 'required|max:255',
             'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            $this->sendError('Validate error', $validator->errors());
        }

        $board->title = $input['title'];
        $board->description = $input['description'];
        
        $board->save();

        return $this->sendResponse(new BoardResource($board), 'The Board has been updated successfully!');

    }

   
    public function destroy(Board $board)
    {
        $errorMessage = [];
        // return response()->json($board, 200);

        if (Auth::user()->id != $board->user_id) {
            return $this->sendError('unauthorized to make this process', $errorMessage);
        }

        $board->delete();
        
        return $this->sendResponse(new BoardResource($board), 'The Board has been deleted successfully!');

    }
}

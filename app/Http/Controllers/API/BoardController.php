<?php

namespace App\Http\Controllers\API;

use App\Models\Board;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\BoardResource as BoardResource;
use App\Http\Resources\TaskResource as TaskdResource;
use App\Http\Resources\LableResource as LabledResource;
use Auth;
use Validator;

class BoardController extends BaseController
{
    
    public function index()
    {
        $board = Board::where('user_id',Auth::user()->id)->get();

        return $this->sendResponse(BoardResource::collection($board), 'Board has been retrieved successfully!');
    }

    
    public function store(Request $request)
    {
        $input = $request->all();
         
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
        ]);
        
        $input['user_id'] = Auth::user()->id;
        $board = Board::create($input);

        return $this->sendResponse(new BoardResource($board), 'The Borad has been created successfully!');
    }

   
    public function show(Board $board)
    {
        // $board = Board::where('id',$board->id)->first();
        
        $errorMessage = [];
        
        // if ($board === null) {
        //     $this->sendError('The board not found', $errorMessage);
        // }

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

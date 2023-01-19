<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BoardController;
use App\Http\Controllers\API\DeveloperController;
use App\Http\Controllers\API\LabelController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\OwnerController;
use App\Http\Controllers\API\TesterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

Route::middleware(['auth:api', 'owner'])->group(function () {
    
    Route::controller(BoardController::class)->group(function () {
        Route::get('/boards', 'index');
        Route::get('/boards/{id}', 'show');
        Route::post('/boards', 'store');
        Route::put('/boards/{board}', 'update');
        Route::delete('boards/{board}', 'destroy');
    });

    Route::controller(TaskController::class)->group(function () {
        Route::get('/tasks/{board}', 'show');
        Route::post('/tasks/{board}', 'store');
        Route::delete('/tasks/{task}', 'destroy');
        Route::put('/tasks/{task}', 'update');

    });

    Route::controller(OwnerController::class)->group(function () {
        Route::get('/owner/task', 'showAllTask');
        Route::post('/assign/{task}', 'assignTask');
        Route::patch('/owner/change-status/{task}', 'changeTaskStatus');
        Route::get('/owner/task-logs', 'showAllTaskLogs');
        Route::get('/owner/task-logs/{task}', 'ShowTaskLogs');


    }); 

    Route::controller(LabelController::class)->group(function () {
        Route::get('/label/{task}', 'show');
        Route::post('/label/{task}', 'store');
        Route::patch('/label/{label}', 'update');
        Route::delete('/label/{label}', 'destroy');
    });
});

Route::middleware(['auth:api', 'developer'])->group(function () {
   
    Route::controller(DeveloperController::class)->group(function () {
        Route::get('/developer/task', 'showAllTask');
        Route::patch('/developer/change-status/{task}', 'changeTaskStatus');

    });
});

Route::middleware(['auth:api', 'tester'])->group(function () {

    Route::controller(TesterController::class)->group(function () {
        Route::get('/tester/task', 'showAllTask');
        Route::patch('/tester/change-status/{task}', 'changeTaskStatus');

    });
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BoardController;
use App\Http\Controllers\API\TaskController;

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
        Route::get('/tasks/{id}', 'show');
        Route::post('/tasks/{board}', 'store');
        Route::delete('/tasks/{task}', 'destroy');
        Route::put('/tasks/{task}', 'update');

    });
});


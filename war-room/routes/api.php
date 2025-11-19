<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('tasks', [\App\Http\Controllers\TaskController::class, 'index']);
    Route::get('tasks/{id}', [\App\Http\Controllers\TaskController::class, 'show']);
    Route::post('tasks', [\App\Http\Controllers\TaskController::class, 'store']);
    Route::put('tasks/{id}', [\App\Http\Controllers\TaskController::class, 'update']);
    Route::delete('tasks/{id}', [\App\Http\Controllers\TaskController::class, 'destroy']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'register']);

Route::post('logins', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('posts', [PostsController::class, 'index']);

Route::post('store', [PostsController::class, 'store']);

Route::get('posts/{id}', [PostsController::class, 'show']);

Route::patch('update/{id}', [PostsController::class, 'update']);

Route::delete('delete/{id}', [PostsController::class, 'delete']);

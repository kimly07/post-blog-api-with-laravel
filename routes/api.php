<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Models\User;
use App\Notifications\SendSmsNotification;
use Illuminate\Support\Facades\Notification;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-send-sms', function () {
    $myNumber = '+8558964606621';
    try {
        Notification::route('twilio', $myNumber)
            ->notify(new SendSmsNotification());

        return response()->json([
            'status' => 'success',
            'message' => 'SMS sent successfully!'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::post('register', [AuthController::class, 'register'])->name('register');

Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::post('logins', [AuthController::class, 'login'])->name('login');

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('posts', [PostsController::class, 'index']);

Route::post('store', [PostsController::class, 'store']);

Route::get('posts/{id}', [PostsController::class, 'show']);

Route::patch('update/{id}', [PostsController::class, 'update']);

Route::delete('delete/{id}', [PostsController::class, 'delete']);


Route::middleware('auth:sanctum')->get('/admin/users', function() {
    $user = auth()->user();

    if ($user->role !== '1') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return User::select('id', 'name', 'email')->get();
})->middleware('auth:sanctum');

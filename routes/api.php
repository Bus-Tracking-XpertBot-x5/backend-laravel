<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;

Route::fallback(function () {
    return response()->json(['message' => 'Page not found!'], 404);
});

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login', [UserAuthController::class, 'login']);
Route::post('/email/verify', [UserAuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/complete-profile', [UserAuthController::class, 'completeProfile']);
    Route::get('/me', [UserAuthController::class, 'me']);
    Route::post('/logout', [UserAuthController::class, 'logout']);
});

Route::post('/forget-password', [UserAuthController::class, 'forgetPassword'])
    ->name('password.reset');

Route::get('users', [UserAuthController::class, 'allUsers']);

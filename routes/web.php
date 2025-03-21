<?php

use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello world!';
});
Route::get('/forget-password', [UserAuthController::class, 'showResetPasswordForm'])
    ->name('password.reset.get');
Route::post('/reset-password', [UserAuthController::class, 'resetPassword'])
    ->name('password.reset.post');

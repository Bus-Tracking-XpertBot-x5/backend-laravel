<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return response()->json(['message' => 'Hello, World!']);
});

Route::get('/login', function () {
    return response()->json(['message' => 'Login here!']);
});

Route::get('/register', function () {
    return response()->json(['message' => 'Register here!']);
});

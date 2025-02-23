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

Route::fallback(function () {
    return response()->json(['message' => 'Page not found!'], 404);
});

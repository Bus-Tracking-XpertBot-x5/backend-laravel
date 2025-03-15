<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello world!';
});

Route::get('/test', function () {
    return 'test!';
});

Route::fallback(function () {
    return response()->json(['message' => 'Page not found!'], 404);
});

Route::get('hi', function () {
    return 'bitch';
});

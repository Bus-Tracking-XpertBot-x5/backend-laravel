<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Hello world!';
});

Route::fallback(function () {
    return response()->json(['message' => 'Page not found!'], 404);
});

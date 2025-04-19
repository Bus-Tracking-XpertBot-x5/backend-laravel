<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        return \response()->json([
            'message' => 'Fetched Successfully!',
            'data' => Route::all()
        ]);
    }
}

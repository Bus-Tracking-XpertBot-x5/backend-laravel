<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\BusMovement;

class BusMovementController extends Controller
{
    use AuthorizesRequests;
    public function index()
{
    $this->authorize('viewAny', BusMovement::class); // This checks if the user can view any bus movements

    $busMovements = BusMovement::all();
    return view('bus_movements.index', compact('busMovements'));
}

public function show(BusMovement $busMovement)
{
    $this->authorize('view', $busMovement); // This checks if the user can view this specific bus movement

    return view('bus_movements.show', compact('busMovement'));
}


}

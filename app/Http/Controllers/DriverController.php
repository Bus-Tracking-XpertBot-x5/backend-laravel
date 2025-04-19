<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Auth;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function getByUserId(Request $request)
    {
        $request->validate([
            'user_id' => "required|exists:users,id"
        ]);

        $driver = Driver::where('user_id', $request->user_id)->first();

        if (!$driver) {
            return response()->json([
                'message' => "Driver Not Found!",
            ], 409);
        }
        return response()->json([
            'message' => "Driver Found Successfully!",
            'driver' => $driver
        ]);
    }

    public function getDriverBuses(Request $request)
    {
        return response()->json([
            'message' => "Buses Found Successfully!",
            'buses' => Auth::user()->driver->buses
        ]);
    }

    public function updateDriverLicense(Request $request)
    {
        $request->validate([
            'license_number' => 'required|string|max:20',
        ]);

        $driver = Driver::where('user_id', Auth::id())->first();

        if (!$driver) {
            return response()->json([
                'message' => 'Driver Not Found!'
            ], 409);
        }

        $driver->update([
            'license_number' => $request->license_number
        ]);
        return response()->json([
            'message' => 'License number updated successfully!'
        ], 200);
    }
}

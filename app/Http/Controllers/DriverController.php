<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\Driver;
use App\Models\PassengerBoarding;
use App\Models\User;
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

    public function notifyPassenger(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Replace this with middlewares
        $driver = Auth::user();

        // Check if the authenticated user is a driver
        if ($driver->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $passenger = User::find($request->user_id);

        SendNotificationJob::dispatch($passenger->device_token, 'Your bus is approaching!.', "Driver {$driver->name} is nearing your location!");

        return response()->json(['message' => 'Passenger notified successfully.'], 200);
    }

    public function triggerPassengerBoardingStatus(Request $request)
    {
        $validatedData = $request->validate([
            'passenger_boarding_id' => 'required|exists:passenger_boardings,id',
            'status' => 'required|in:scheduled,boarded,missed',
        ]);

        $passengerBoarding = PassengerBoarding::with('movement')->findOrFail($validatedData['passenger_boarding_id']);
        $movement = $passengerBoarding->movement;

        if ($passengerBoarding->status === 'boarded') {
            return response()->json(['message' => "Already boarded!"], 400);
        }
        if ($movement->status === 'scheduled') {
            return response()->json(['message' => "Trip hasn't started yet!"], 400);
        }

        if ($movement->status === 'completed') {
            return response()->json(['message' => "Trip has ended!"], 400);
        }

        if ($movement->status === 'cancelled') {
            return response()->json(['message' => "Trip has been cancelled!"], 400);
        }

        $passengerBoarding->update([
            'status' => $validatedData['status'],
        ]);

        if ($validatedData['status'] === 'boarded') {
            $movement->increment('actual_passenger_count');
        }

        return response()->json([
            'message' => 'Status updated successfully!',
            'data' => [
                'actual_passenger_count' => $movement->actual_passenger_count,
                'passengerBoarding' => $passengerBoarding
            ]
        ], 200);
    }
}

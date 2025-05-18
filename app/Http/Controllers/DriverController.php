<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\Driver;
use App\Models\PassengerBoarding;
use App\Models\User;
use App\Models\Bus;
use App\Models\BusMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'status' => 'required|in:scheduled,boarded,missed,departed',
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

    public function getScheduledPassengers()
    {
        $driver = Auth::user()->driver;

        $activeBus = Bus::where('driver_id', $driver->id)
            ->where('status', 'active')
            ->first();

        if (!$activeBus) {
            return response()->json(['message' => 'No active bus found'], 404);
        }

        $scheduledMovement = BusMovement::where('bus_id', $activeBus->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$scheduledMovement) {
            return response()->json([
                'count' => 0,
                'passengers' => 0
            ]);
        }

        $boardingUserIds = PassengerBoarding::where('movement_id', $scheduledMovement->id)
            ->where('status', 'scheduled')
            ->pluck('user_id');

        $passengers = User::whereIn('id', $boardingUserIds)->get(['id', 'name']);
        $count = User::whereIn('id', $boardingUserIds)->count();

        return response()->json([
            'count' => $count,
            'passengers' => $passengers
        ]);
    }

    public function getBoardedPassengers()
    {
        $driver = Auth::user()->driver;

        $activeBus = Bus::where('driver_id', $driver->id)
            ->where('status', 'active')
            ->first();

        if (!$activeBus) {
            return response()->json(['message' => 'No active bus found'], 404);
        }

        $scheduledMovement = BusMovement::where('bus_id', $activeBus->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$scheduledMovement)
            return response()->json([
                'count' => 0,
                'passengers' => 0
            ]);

        $boardingUserIds = PassengerBoarding::where('movement_id', $scheduledMovement->id)
            ->where('status', 'boarded')
            ->pluck('user_id');

        $passengers = User::whereIn('id', $boardingUserIds)->get(['id', 'name']);
        $count = User::whereIn('id', $boardingUserIds)->count();

        return response()->json([
            'count' => $count,
            'passengers' => $passengers
        ]);
    }

    public function getMonthlyMoney()
    {
        $currentMonth = Carbon::now()->month;

        $driver = Auth::user()->driver;

        $buses =  Bus::where('driver_id', $driver->id)->get();

        $busIds =  $buses->pluck('id');

        $trips = BusMovement::whereIn('bus_id', $busIds)
            ->where('status', 'completed')
            ->whereMonth('actual_start', $currentMonth)
            ->with(['passengerBoardings' => function ($query) {
                $query->where('status', 'boarded');
            }])
            ->get();

        $totalMoney = $trips->reduce(function ($carry, $trip) {
            $passengerCount = $trip->passengerBoardings->count();
            $tripRevenue = $passengerCount * $trip->price;
            return $carry + $tripRevenue;
        }, 0);

        return response()->json([
            'totalMoney' =>  $totalMoney
        ]);
    }

    public function getMonthlyTime()
    {
        $currentMonth = Carbon::now()->month;

        $driver = Auth::user()->driver;

        $buses =  Bus::where('driver_id', $driver->id)->get();

        $busIds =  $buses->pluck('id');

        $trips = BusMovement::whereIn('bus_id', $busIds)
            ->where('status', 'completed')
            ->whereMonth('actual_start', $currentMonth);

        $totalMinutes = $trips->get()->sum(function ($trip) {
            return Carbon::parse($trip->actual_start)->diffInMinutes($trip->actual_end);
        });

        $hours = floor($totalMinutes / 60);
        $remaining_minutes = $totalMinutes % 60;

        return response()->json([
            'hours' => $hours,
            'minutes' => $remaining_minutes
        ]);
    }
}

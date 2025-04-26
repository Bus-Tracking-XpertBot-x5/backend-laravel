<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\BusMovement;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BusMovementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === "passenger") {
            return response()->json(
                BusMovement::with(['bus.driver.user', 'route', 'organization'])
                    ->where(['status' => 'scheduled', 'organization_id' => $user->organization_id])->get(),
                200
            );
        }

        return response()->json(
            BusMovement::with(['bus.driver.user', 'route', 'organization'])
                ->where('status', 'scheduled')->get(),
            200
        );
    }

    public function getDriverTrips(Request $request)
    {
        $query = BusMovement::with(['bus.driver.user', 'route', 'organization'])
            ->whereHas('bus.driver', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderBy('estimated_start', 'desc');

        if ($request->search !== null && $request->search !== "all") {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('route_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status !== null && $request->status !== "all") {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->get(),
            200
        );;
    }

    public function getSingleTrip(Request $request)
    {
        $request->validate([
            'movement_id' => "required|exists:bus_movements,id"
        ]);

        return response()->json(
            BusMovement::with(['bus.driver.user', 'route', 'organization', 'bus.driver', 'passengerBoardings.user'])
                ->find($request->movement_id),
            200
        );;
    }

    public function triggerStatus(Request $request)
    {
        $validatedData = $request->validate([
            'movement_id' => "required|exists:bus_movements,id",
            "status" => "required|in:scheduled,in_progress,completed,cancelled"
        ]);

        $trip = BusMovement::findOrFail($request->movement_id);

        if ($request->status === 'in_progress') {
            // Retrieve all passenger boardings registered for this trip
            $boardedPassengers =
                $trip
                ->passengerBoardings()
                ->with('user')
                ->where('status', 'scheduled')
                ->get();

            if (count($boardedPassengers) == 0) {
                return response()->json([
                    'message' => 'No passengers registered yet!',
                ], 409);
            } else {
                $trip->update([
                    'status' => "in_progress",
                    "actual_start" => Carbon::now()
                ]);

                foreach ($boardedPassengers as $value) {
                    SendNotificationJob::dispatch($value->user->device_token, 'Trip has started.', "The trip " . $trip->route->route_name . " has started!");
                }

                return response()->json([
                    'message' => 'Trip has started Successfully!',
                    'data' => $trip
                ], 200);
            }
        } else if ($request->status === 'completed') {
            // Retrieve all passenger boardings with status 'boarded' for this trip
            $boardedPassengers =
                $trip
                ->passengerBoardings()
                ->with('user')
                ->where('status', 'boarded')
                ->get();

            if (count($boardedPassengers) == 0) {
                return response()->json([
                    'message' => 'No passengers boarded yet!',
                ], 409);
            }
            if (count($boardedPassengers) === $trip->passengerBoardings()->count()) {
                $trip->update([
                    'status' => "completed",
                    "actual_end" => Carbon::now()
                ]);

                foreach ($boardedPassengers as $value) {
                    SendNotificationJob::dispatch($value->user->device_token, 'Trip has ended.', "The trip " . $trip->route->route_name . " has ended!");
                }

                return response()->json([
                    'message' => 'Status Updated Successfully!',
                    'data' => $trip
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Passengers still not on board!',
                ], 409);
            }
        } else if ($request->status === 'cancelled') {
            // Retrieve all passenger boardings with status 'boarded' for this trip
            $boardedPassengers =
                $trip
                ->passengerBoardings()
                ->with('user')
                ->where('status', 'boarded')
                ->get();

            if (count($boardedPassengers) == 0) {
                $trip->update([
                    'status' => "cancelled",
                ]);
                foreach ($boardedPassengers as $value) {
                    SendNotificationJob::dispatch($value->user->device_token, 'Trip has been cancelled!.', "The trip " . $trip->route->route_name . " has been cancelled!");
                }
                return response()->json([
                    'message' => 'Trip cancelled successfully!',
                ], 200);
            } else {
                return response()->json([
                    'message' => "Passengers registered! Trip can't be cancelled!",
                ], 409);
            }
        }


        return response()->json([
            'message' => 'Status Updated Successfully!',
        ], 200);
    }

    public function createTrip(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'route_id' => 'required|exists:routes,id',
            'bus_id' => 'required|exists:buses,id',
            'estimated_start' => 'required|date',
            'estimated_end' => 'required|date|after:estimated_start',
            'price' => "required"
        ]);

        $start = Carbon::parse($request['estimated_start']);
        $end = Carbon::parse($request['estimated_end']);

        // Check if both dates are on the same day
        if (!$start->isSameDay($end)) {
            return response()->json([
                'message' => 'The estimated start and end times must be on the same day.',
            ], 422);
        }

        $overlap =
            BusMovement::where('estimated_start', '<=', $request->estimated_end)
            ->where('estimated_end', '>=', $request->estimated_start)
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'Trip time overlaps with another one!',
            ], 409);
        }

        BusMovement::create([
            ...$request->all(),
            'status' => 'scheduled'
        ]);

        return response()->json([
            'message' => 'Trip created successfully',
        ], 200);
    }

    public function getTripBoardings(Request $request)
    {
        $request->validate([
            'movement_id' => 'required|exists:bus_movements,id',
            'status' => 'nullable|in:all,scheduled,boarded,missed',
            'search' => 'nullable|string'
        ]);

        $trip = BusMovement::find($request->movement_id);

        $query = $trip
            ->passengerBoardings()
            ->with(['user']);

        if ($request->status !== null && $request->status !== "all") {
            $query->where('status', $request->status);
        }

        if ($request->search !== null && $request->search !== "all") {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $data = $query->get();

        if (!$data) {
            return response()->json([
                'message' => 'No Passenger Boardings Found!',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Trips found successfully!',
            'data' => $data
        ], 200);
    }
}

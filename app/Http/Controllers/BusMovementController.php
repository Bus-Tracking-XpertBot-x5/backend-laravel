<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\BusMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        );
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
            response()->json([
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'estimated_end' => [
                        'The estimated start and end times must be on the same day.'
                    ],
                ],
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

    public function getNextTrip(Request $request)
    {
        $user = Auth::user();

        $nextTrip = $user->passengerBoardings()
            ->where('status', 'scheduled')
            ->with(['movement' => function ($query) {
                $query->with(['bus.driver.user', 'route']);
            }])
            ->orderBy('estimated_boarding_time', 'asc')
            ->first();

        if (!$nextTrip) {
            return response()->json([
                'message' => 'No upcoming trips found!',
                'data' => null
            ], 404);
        }

        $data = [
            'estimated_boarding_time' => $nextTrip->estimated_boarding_time,
            'route_name' => $nextTrip->movement->route->route_name,
            'bus_name' => $nextTrip->movement->bus->name,
            'driver_name' => $nextTrip->movement->bus->driver->user->name,
            'estimated_end' => $nextTrip->movement->estimated_end
        ];

        return response()->json([
            'message' => 'Next trip found successfully!',
            'data' => $data
        ], 200);
    }

    public function getCurrentTrip(Request $request)
    {
        $user = Auth::user();

        $boarded_trip_boarding = $user->passengerBoardings()
            ->where('status', 'boarded')
            ->with(['movement.bus.driver.user', 'movement.route'])
            ->first();

        if (!$boarded_trip_boarding) {
            return response()->json([
                'message' => 'No current trips found!',
                'data' => null
            ], 404);
        }

        $current_trip = $boarded_trip_boarding->movement;
        $remaining_passengers = $current_trip->passengerBoardings()
            ->where('status', 'scheduled')
            ->count();

        $data = [
            'passengers' => $current_trip->actual_passenger_count,
            'remaining_passengers' => $remaining_passengers,
            'route' => $current_trip->route->route_name,
            'bus' => $current_trip->bus->name,
            'driver' => $current_trip->bus->driver->user->name,
            'estimated_end' => $current_trip->estimated_end
        ];

        return response()->json([
            'message' => 'Current trip found successfully!',
            'data' => $data
        ], 200);
    }

    public function getTripStatistics(Request $request)
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();

        $statistics = $user->passengerBoardings()
            ->where('created_at', '>=', $startOfMonth)
            ->get()
            ->groupBy('status')
            ->map(function ($boardings) {
                return $boardings->count();
            });

        return response()->json([
            'message' => 'Trip statistics retrieved successfully',
            'data' => [
                'total_trips' => $statistics->sum(),
                'completed' => $statistics->get('boarded', 0),
                'missed' => $statistics->get('missed', 0),
                'scheduled' => $statistics->get('scheduled', 0)
            ]
        ], 200);
    }
}

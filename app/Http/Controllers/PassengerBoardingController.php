<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationJob;
use App\Models\BusMovement;
use App\Models\PassengerBoarding;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PassengerBoardingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:all,scheduled,boarded,missed',
            'search' => 'nullable|string'
        ]);

        $query = Auth::user()
            ->passengerBoardings()
            ->with(['movement.bus.driver.user', 'movement.route', 'movement.organization', 'user']);

        if ($request->status !== null && $request->status !== "all") {
            $query->where('status', $request->status);
        }

        if ($request->search !== null && $request->search !== "all") {
            $query->whereHas('movement.route', function ($q) use ($request) {
                $q->where('route_name', 'like', '%' . $request->search . '%');
            });
        }

        $data = $query->get();


        if (!$data) {
            return response()->json([
                'message' => 'No trips taken!',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Trips found successfully!',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'movement_id' => 'required|exists:bus_movements,id',
        ]);
        $validatedData['user_id'] = Auth::id();

        if (PassengerBoarding::where([
            'movement_id' =>
            $validatedData['movement_id'],
            'user_id' => $validatedData['user_id']
        ])->first()) {
            return response()->json([
                'message' => 'Already registered in this trip!'
            ], 401);
        }

        $movement = BusMovement::with('bus')->findOrFail($validatedData['movement_id']);

        // Check for overlapping trips:
        // We consider overlapping if the new movement's time range intersects with any scheduled trips the user has.
        $overlap = PassengerBoarding::where('user_id', $validatedData['user_id'])
            ->whereHas('movement', function ($query) use ($movement) {
                $query->where('estimated_start', '<=', $movement->estimated_end)
                    ->where('estimated_end', '>=', $movement->estimated_start);
            })->with('movement')->first();

        // dd($overlap, $movement);
        if ($overlap) {
            $formattedStart = \Carbon\Carbon::parse($overlap->movement->estimated_start)->format('g:i A');
            $formattedEnd = \Carbon\Carbon::parse($overlap->movement->estimated_end)->format('g:i A');

            return response()->json([
                'message' =>
                "Your selected trip overlaps with an existing trip on the same day from {$formattedStart} to {$formattedEnd}."
            ], 409);
        }

        // Check if the bus has reached its capacity
        $passengerCount = PassengerBoarding::where('movement_id', $validatedData['movement_id'])->count();
        if ($passengerCount >= $movement->bus->capacity || $movement->booked_passenger_count >= $movement->bus->capacity) {
            return response()->json([
                'message' => 'This trip is fully booked.'
            ], 409);
        }

        if ($movement->booked_passenger_count < $movement->bus->capacity) {
            $movement->booked_passenger_count += 1;
            $movement->save();
        }

        $validatedData['status'] = 'scheduled';

        // this below should be calculated
        $validatedData['estimated_boarding_time'] = Carbon::now();

        $created = PassengerBoarding::create($validatedData);

        $created->load(['movement.bus.driver.user', 'movement.route', 'movement.organization', 'user']);

        SendNotificationJob::dispatch(Auth::user()->device_token, 'Passenger Boarded Successfully!.', 'You boarded successfully in a trip!');

        return response()->json($created);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusMovement extends Model
{
    protected $fillable = [
        'bus_id',
        'route_id',
        'estimated_start',
        'estimated_end',
        'actual_start',
        'actual_end',
        'passenger_count',
        'status',
        'booked_passenger_count',
        'actual_passenger_count',
        'organization_id',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function locations()
    {
        return $this->hasOne(BusLocation::class);
    }

    public function passengerBoardings()
    {
        return $this->hasMany(PassengerBoarding::class, 'movement_id', 'id');
    }
}

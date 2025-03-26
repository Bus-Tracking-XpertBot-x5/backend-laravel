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
        'status'
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
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
        return $this->hasMany(PassengerBoarding::class);
    }
}
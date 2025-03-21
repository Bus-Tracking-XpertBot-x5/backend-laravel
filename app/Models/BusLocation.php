<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusLocation extends Model
{
    /** @use HasFactory<\Database\Factories\BusLocationFactory> */
    use HasFactory;
    protected $fillable = [
        'actual_start_time'
        ,'actual_end_time'
        ,'status'
        ,'current_location'
        ,'route_id'
        ,'bus_id'
    ];

    public function route(){
        return $this->belongsTo(Route::class);
    }
    public function passengerBoarding(){
        return $this->hasMany(PassengerBoarding::class);
    }
    public function bus(){
        return $this->belongsTo(Bus::class);
    }
}

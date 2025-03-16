<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerBoarding extends Model
{
    /** @use HasFactory<\Database\Factories\PassengerBoardingFactory> */
    use HasFactory;
    protected $fillable = [
        'current_passenger_count',
        'actual_boarding_time',
        'actual_exit_time',
        'estimated_boarding_time',
        'estimated_exit_time',
        'status',
        'passenger_id',
        'route_id',
        'bus_location_id',
        'date'
    ];

    public function route(){
        return $this->belongsTo(Route::class);
    }
    public function passenger(){
        return $this->belongsTo(Passenger::class);
    }
    public function busLocation(){
        return $this->belongsTo(BusLocation::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($boarding) {
            if ($boarding->isDirty('status') && $boarding->status === 'boarded') {
                $boarding->date = now()->toDateString();
            }
        });
    }
}

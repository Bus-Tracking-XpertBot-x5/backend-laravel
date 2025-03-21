<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    /** @use HasFactory<\Database\Factories\RouteFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'days_of_week',
        'from_location',
        'to_location',
        'organization_id',
        'bus_id'
    ];

    public function organizations(){
        return $this->belongsToMany(Organization::class,'organization_route');
    }
    public function buses(){
        return $this->belongsToMany(Bus::class,'bus_route');
    }
    public function passengerBoardings(){
        return $this->hasMany(PassengerBoarding::class);
    }
    public function busLocations(){
        return $this->hasMany(BusLocation::class);
    }
    
}

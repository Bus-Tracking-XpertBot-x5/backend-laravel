<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    /** @use HasFactory<\Database\Factories\BusFactory> */
    use HasFactory;

    protected $fillable = [
        'bus_number'
        ,'max_passengers'
        ,'status'
        ,'organization_id'
        ,'driver_id'
    ];

    public function busLocation(){
        return $this->hasOne(BusLocation::class);
    }
    public function organizations(){
        return $this->belongsToMany(Organization::class,'bus_organization');
    }
    public function drivers(){
        return $this->belongsToMany(Driver::class,'bus_driver');
    }
    public function routes(){
        return $this->belongsToMany(Route::class,'organization_route');
    }
}

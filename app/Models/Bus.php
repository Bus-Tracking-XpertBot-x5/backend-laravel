<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{


    protected $fillable = [
        'name',
        'license_plate',
        'capacity',
        'status',
        'driver_id'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)->using(BusOrganization::class);
    }

    public function movements()
    {
        return $this->hasMany(BusMovement::class);
    }
}

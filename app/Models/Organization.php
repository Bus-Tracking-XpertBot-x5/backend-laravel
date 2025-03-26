<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{


    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'manager_id'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function buses()
    {
        return $this->belongsToMany(Bus::class)->using(BusOrganization::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(Driver::class)->using(DriverOrganization::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}

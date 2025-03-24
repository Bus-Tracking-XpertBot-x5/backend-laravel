<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{

    protected $fillable = [
        'name',
        'user_id',
        'license_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buses()
    {
        return $this->hasMany(Bus::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class)->using(DriverOrganization::class);
    }
}
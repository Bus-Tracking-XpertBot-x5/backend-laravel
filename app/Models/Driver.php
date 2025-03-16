<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /** @use HasFactory<\Database\Factories\DriverFactory> */
    use HasFactory;

    protected $fillable = [
    'license_number'
    ,'license_expiry'
    ,'user_id'
    ,'organization_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function organizations(){
        return $this->belongsToMany(User::class,'driver_organization');
    }
    public function buses(){
        return $this->belongsToMany(Bus::class,'bus_driver');
    }
}

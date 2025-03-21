<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    protected $fillable = [
        'name'
        ,'email'
        ,'email_verified_at'
        ,'phone'
        ,'phone_verified_at'
        ,'type'
        ,'description'
        ,'location'
        ,'manager_id'
    ];

    public function manager(){
        return $this->belongsTo(User::class);
    }
    public function passengers(){
        return $this->belongsToMany(Passenger::class,'organization_passenger');
    }
    public function drivers(){
        return $this->belongsToMany(Driver::class,'driver_organization');
    }
    public function buses(){
        return $this->belongsToMany(Bus::class,'bus_organization');
    }
    public function routes(){
        return $this->belongsToMany(Route::class,'organization_route');
    }
}

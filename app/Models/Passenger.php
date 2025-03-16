<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    /** @use HasFactory<\Database\Factories\PassengerFactory> */
    use HasFactory;
    protected $fillable = [
        'subscription_status',
        'location',
        'organization_id',
        'user_id'
    ];

    public function organizations(){
        return $this->belongsToMany(Organization::class,'organization_passenger');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function passengerBoardings(){
        return $this->hasMany(PassengerBoarding::class);
    }
}

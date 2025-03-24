<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassengerBoarding extends Model
{
    protected $fillable = [
        'movement_id',
        'user_id',
        'estimated_boarding_time',
        'status'
    ];

    public function movement()
    {
        return $this->belongsTo(BusMovement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

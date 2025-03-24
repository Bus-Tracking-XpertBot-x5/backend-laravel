<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusLocation extends Model
{

    protected $fillable = [
        'latitude',
        'longitude',
        'movement_id'
    ];

    public function movement()
    {
        return $this->belongsTo(BusMovement::class);
    }
}

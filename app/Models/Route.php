<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'route_name',
        'start_location',
        'end_location'
    ];

    protected $casts = [
        'start_location' => 'array',
        'end_location' => 'array',
    ];

    public function movements()
    {
        return $this->hasMany(BusMovement::class);
    }
}
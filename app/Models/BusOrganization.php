<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BusOrganization extends Pivot
{
    protected $table = 'bus_organization';
}

class DriverOrganization extends Pivot
{
    protected $table = 'driver_organization';
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DriverOrganization extends Pivot
{
    protected $table = 'driver_organization';

    public $incrementing = true;
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

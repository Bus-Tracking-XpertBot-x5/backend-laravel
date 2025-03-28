<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BusOrganization extends Pivot
{
    protected $table = 'bus_organization';

    public $incrementing = true;

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

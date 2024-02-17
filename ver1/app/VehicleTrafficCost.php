<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleTrafficCost extends Model
{
    use SoftDeletes;

    protected $table = "vehicle_traffic_costs";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'vehicle_type',
            'traffic_cost_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

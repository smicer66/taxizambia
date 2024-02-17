<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrafficCost extends Model
{
    use SoftDeletes;

    protected $table = "traffic_costs";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'base_fare',
            'district_id',
            'district_name',
            'cancellationFare',
            'minimumFare',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

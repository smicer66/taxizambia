<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleTracker extends Model
{
    //
    use SoftDeletes;

    protected $table = "vehicle_trackers";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'vehicle_id',
            'vehicle_unique_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

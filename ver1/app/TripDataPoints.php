<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripDataPoints extends Model
{
    //
    use SoftDeletes;

    protected $table = "trip_data_points";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'latitude',
            'longitude',
            'deal_id',
            'trip_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

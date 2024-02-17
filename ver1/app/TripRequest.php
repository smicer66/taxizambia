<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "trip_requests";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'passenger_user_id',
            'status',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

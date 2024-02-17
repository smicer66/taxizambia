<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripCancelation extends Model
{
    //
    use SoftDeletes;

    protected $table = "trip_cancelations";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'cancelation_reason_id',
            'trip_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

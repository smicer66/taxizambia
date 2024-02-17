<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sos extends Model
{
    //
    use SoftDeletes;

    protected $table = "sos_messages";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'tripId',
            'latitude',
            'longitude',
            'sentByUserId',
            'vicinity',
            'sentByUserMobileNumber',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

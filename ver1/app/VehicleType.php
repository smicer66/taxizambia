<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    //
    use SoftDeletes;

    protected $table = "vehicle_types";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'name',
            'status',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    //
    use SoftDeletes;

    protected $table = "vehicles";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'vehicle_maker',
            'vehicle_type',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

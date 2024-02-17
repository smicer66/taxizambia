<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    //
    use SoftDeletes;

    protected $table = "cities";

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

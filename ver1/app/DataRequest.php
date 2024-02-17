<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "data_requests";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'data_request',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CancelationReason extends Model
{
    //
    use SoftDeletes;

    protected $table = "cancelation_reasons";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'name',
            'type',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

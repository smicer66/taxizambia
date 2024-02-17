<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    //
    use SoftDeletes;

    protected $table = "merchants";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'merchantName',
            'merchantCode',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

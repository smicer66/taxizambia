<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Junk extends Model
{
    //
    use SoftDeletes;

    protected $table = "junks";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'data',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BevuraToken extends Model
{
    //
    use SoftDeletes;

    protected $table = "bevura_tokens";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'bevura_token',
            'user_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

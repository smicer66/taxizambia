<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportMessage extends Model
{
    //
    use SoftDeletes;

    protected $table = "support_messages";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'message',
            'user_id',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "card_requests";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'name',
            'email',
			'phone',
            'message',
            'card_type'
        ];

    protected $hidden =
        [
            '_token',
        ];
}

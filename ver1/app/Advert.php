<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advert extends Model
{
    //
    use SoftDeletes;

    protected $table = "advertisements";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'image_url',
            'status',
            'uploaded_by_user_id',
            'redirect_url',
            'display_end_date',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

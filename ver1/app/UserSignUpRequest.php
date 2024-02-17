<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSignUpRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "user_signup_requests";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'mobile_number',
            'otp',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

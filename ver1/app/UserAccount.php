<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccount extends Model
{
    //
    use SoftDeletes;

    protected $table = "useraccounts";

    protected $date = "deleted_at";

    protected $fillable =
    [
        'id',
        'accountType',
        'status',
        'accountNumber',
        'userId',
    ];

    protected $hidden =
    [
        '_token',
    ];
}

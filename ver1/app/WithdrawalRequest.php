<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "withdrawal_requests";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'vehicle_maker',
            'vehicle_type',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

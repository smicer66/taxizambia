<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantTransaction extends Model
{
    //
    use SoftDeletes;

    protected $table = "merchanttransactions";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'merchantId',
            'txnReference',
            'amount',
            'sourceAccountId',
            'narration'
        ];

    protected $hidden =
        [
            '_token',
        ];
}

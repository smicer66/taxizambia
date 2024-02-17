<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileTelcoTransaction extends Model
{
    //
    use SoftDeletes;

    protected $table = "mobiletelcotransactions";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'mobiletelcoId',
            'txnReference',
            'amount',
            'sourceAccountId'
        ];

    protected $hidden =
        [
            '_token',
        ];
}

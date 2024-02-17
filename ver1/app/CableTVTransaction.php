<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CableTVTransaction extends Model
{
    //
	use SoftDeletes;

    protected $table = "cabletvtransactions";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'txnReference',
            'cableTVProductId',
            'destinationAccountId',
            'amount',
            'sourceAccountId',
        ];

    protected $hidden =
        [
            '_token',
        ];
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightTransaction extends Model
{
    //
    use SoftDeletes;

    protected $table = "flighttransactions";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'txnReference',
            'airlineId',
            'destinationAccountId',
            'amount',
            'sourceAccountId',
            'narration',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

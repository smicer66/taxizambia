<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionBreakdown extends Model
{
    //
    use SoftDeletes;

    protected $table = "transaction_breakdown";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'user_id',
            'transaction_type',
            'transaction_id',
            'trip_id',
            'deal_id',
            'breakdown_amount',
            'is_reversed',
            'details',
            'is_credit',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

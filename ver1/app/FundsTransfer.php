<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundsTransfer extends Model
{
    //use Illuminate\Database\Eloquent\SoftDeletes;
    use SoftDeletes;

    protected $table = "fundstransfers";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'sourceAccount',
            'receipientAccount',
            'sourceUserAccountId',
            'receipientUserAccountId',
            'amount',
            'charges',
            'narration',
            'bank',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

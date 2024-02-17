<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfirmCheque extends Model
{
    //
    use SoftDeletes;

    protected $table = "confirmcheques";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'chequeAcctId',
            'chequeNumber',
            'beneficiaryAcctNumber',
            'amount',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

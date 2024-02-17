<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChequeBookRequest extends Model
{
    //
    use SoftDeletes;

    protected $table = "chequebookrequest";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'leaves',
            'branchId',
            'chequeAcctId',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

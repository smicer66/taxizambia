<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StopCheque extends Model
{
    //
    use SoftDeletes;

    protected $table = "stopcheque";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'sendingAcctId',
            'chequeNumber',
            'narration',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

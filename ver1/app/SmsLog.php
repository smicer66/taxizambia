<?php


namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsLog extends Model
{

    use SoftDeletes;

    protected $table = "sms_logs";

    protected $fillable =
        [
            'id',
            'receipient_no',
            'response',
            'message',
            'success',
            'user_id'
        ];

    protected $hidden =
        [
            '_token',
        ];

}

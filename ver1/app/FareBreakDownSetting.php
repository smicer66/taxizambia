<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FareBreakDownSetting extends Model
{
    //
    use SoftDeletes;

    protected $table = "fare_breakdown_settings";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'status',
            'title',
            'value_percent',
	     'fixed_amount',
            'is_withdrawable',
            'details',
        ];

    protected $hidden =
        [
            '_token',
        ];
}

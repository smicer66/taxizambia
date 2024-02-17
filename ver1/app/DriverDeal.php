<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverDeal extends Model
{
    //
    use SoftDeletes;

    protected $table = "driver_deals";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'vehicle_id',
            'passenger_user_id',
        ];

    protected $hidden =
        [
            '_token',
        ];



	public function passenger(){
        	return $this->hasOne(User::class,'id','passenger_user_id');
    	}
}

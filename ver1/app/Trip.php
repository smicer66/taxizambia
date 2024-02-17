<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    //
    use SoftDeletes;

    protected $table = "trips";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'trip_identifier',
            'origin_longitude',
        ];

    protected $hidden =
        [
            '_token',
        ];


	public function passenger_user(){
        	return $this->hasOne(User::class,'id','passenger_user_id');
    	}
}

<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    use SoftDeletes;
	
	protected $table = "transactions";

    protected $fillable = [
        'orderId', 'transactionRef', 'requestData','payeeUserId', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];
}

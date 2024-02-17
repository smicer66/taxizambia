<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Province extends Model
{

    use SoftDeletes;

    protected $table = "provinces";

    protected $fillable =
        [
            'id',
            'name',
            'code',
            'status',
            'env',
        ];

    protected $hidden =
        [
            '_token',
        ];

    public function country() {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }

}

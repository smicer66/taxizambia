<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    //
    use SoftDeletes;

    protected $table = "districts";

    protected $date = "deleted_at";

    protected $fillable =
        [
            'id',
            'name'
        ];

    protected $hidden =
        [
            '_token',
        ];

    public function province() {
        return $this->hasOne(Province::class, 'id', 'provinceId');
    }
}

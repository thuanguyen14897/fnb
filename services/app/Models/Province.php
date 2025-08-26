<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'tbl_province';

    function service()
    {
        return $this->hasMany('App\Models\Service', 'province_id', 'Id');
    }

}

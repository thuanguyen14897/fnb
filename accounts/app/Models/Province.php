<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'tbl_province';

    function car()
    {
        return $this->hasMany('App\Models\Car', 'province_id', 'Id');
    }

    function carActive()
    {
        return $this->hasMany('App\Models\Car', 'province_id', 'Id')->where('status',1);
    }
}

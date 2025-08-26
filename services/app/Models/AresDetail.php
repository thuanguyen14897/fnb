<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AresDetail extends Model
{
    use HasFactory;
    protected $table = 'tbl_ares_detail';

    function province()
    {
        return $this->belongsTo('App\Models\Province', 'id_province', 'Id');
    }
}

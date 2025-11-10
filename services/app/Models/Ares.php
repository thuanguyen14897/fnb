<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ares extends Model
{
    use HasFactory;
    protected $table = 'tbl_ares';

    function ares_ward()
    {
        return $this->belongstoMany('App\Models\Ward', 'tbl_ares_ward', 'id_ares', 'id_ward');
    }

    function ares_province()
    {
        return $this->belongstoMany('App\Models\Province', 'tbl_ares_detail', 'id_ares', 'id_province');
    }

    function aresWard()
    {
        return $this->hasMany('App\Models\AresWard', 'id_ares', 'id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AresWard extends Model
{
    use HasFactory;
    protected $table = 'tbl_ares_ward';

    function ward()
    {
        return $this->belongsTo('App\Models\Ward', 'id_ward', 'Id');
    }

    function ares()
    {
        return $this->belongsTo('App\Models\Ares', 'id_ares', 'id');
    }
}

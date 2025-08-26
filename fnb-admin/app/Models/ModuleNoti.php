<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleNoti extends Model
{
    use HasFactory;
    protected $table = 'tbl_module_noti';

    function day()
    {
        return $this->hasMany('App\Models\ModuleNotiDay', 'module_noti_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}

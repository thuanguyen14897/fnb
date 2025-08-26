<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleNotiDay extends Model
{
    use HasFactory;
    protected $table = 'tbl_module_noti_day';

    function module_noti()
    {
        return $this->belongsTo('App\Models\ModuleNoti', 'module_noti_id', 'id');
    }
}

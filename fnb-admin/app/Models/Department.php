<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $table='tbl_department';


    function user()
    {
        return $this->belongstoMany('App\Models\User', 'tbl_user_department', 'department_id', 'user_id');
    }
}

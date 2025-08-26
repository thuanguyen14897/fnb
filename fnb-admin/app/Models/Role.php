<?php

namespace App\Models;

use App\Traits\RoleTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use RoleTrait;
    protected $table = 'tbl_roles';

    function user()
    {
        return $this->belongstoMany('App\Models\User', 'tbl_role_user', 'role_id', 'user_id');
    }

    function permission()
    {
        return $this->belongstoMany('App\Models\Permission', 'tbl_permission_role', 'role_id', 'permission_id');
    }
}

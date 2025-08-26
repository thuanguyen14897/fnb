<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPermission extends Model
{
    use HasFactory;
    protected $table = 'tbl_group_permissions';

    function permission()
    {
        return $this->hasMany('App\Models\Permission', 'group_permission_id', 'id');
    }

    function role()
    {
        return $this->belongstoMany('App\Models\Role', 'tbl_permission_role', 'group_permission_id', 'role_id');
    }
}

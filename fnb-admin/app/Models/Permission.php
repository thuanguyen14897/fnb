<?php

namespace App\Models;

use App\Traits\PermissionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use PermissionTrait;
    protected $table = 'tbl_permissions';

    function groupPermission()
    {
        return $this->belongsTo('App\Models\GroupPermission', 'group_permission_id', 'id');
    }

    function role()
    {
        return $this->belongstoMany('App\Models\Role', 'tbl_permission_role', 'permission_id', 'role_id');
    }

    function user()
    {
        return $this->belongstoMany('App\Models\User', 'tbl_user_permission', 'permission_id', 'user_id');
    }
}

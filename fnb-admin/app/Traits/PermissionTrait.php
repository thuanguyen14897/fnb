<?php

namespace App\Traits;

use App\Models\Role;

use Illuminate\Support\Facades\Config;

trait PermissionTrait
{
    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * Many-to-Many relations with role model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongstoMany('App\Models\Role', 'tbl_permission_role', 'permission_id', 'role_id');
    }
}

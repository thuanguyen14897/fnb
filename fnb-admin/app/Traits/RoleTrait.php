<?php


namespace App\Traits;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait RoleTrait
{
    public function cachedPermissions()
    {
        $rolePrimaryKey = $this->primaryKey;
        $cacheKey = 'permissions_for_role_'.$this->$rolePrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags('permission_role')->remember($cacheKey, Config::get('cache.ttl', 60),
                function () {
                    return $this->permissions()->get();
                });
        } else {
            return $this->permissions()->get();
        }

    }

    public function flushCache()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags('permission_role')->flush();
        }

        return $this;
    }


    /**
     * Boot the role model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the role model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($role) {

            $role->users()->sync([]);
            $role->permissions()->sync([]);

            return true;
        });
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongstoMany('App\Models\User', 'tbl_role_user', 'role_id', 'user_id');
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongstoMany('App\Models\Permission', 'tbl_permission_role', 'role_id', 'permission_id');
    }

    /**
     * Checks if the role has a permission by its name.
     *
     * @param string|array $name Permission name or array of permission names.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($permisson_parent = '', $name, $requireAll = false)
    {
        if (empty($permisson_parent)) {
            return false;
        }
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            foreach ($this->cachedPermissions() as $permission) {
                if ($permission->display_name == $name && $permission->groupPermisson->display_name == $permisson_parent) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param mixed $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        if (!empty($permissions)) {
            $this->permissions()->sync($permissions);
        } else {
            $this->permissions()->detach();
        }

        $this->flushCache();

        return $this;
    }

    /**
     * Attach permission to current role.
     *
     * @param object|array $permission
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_string($permission)) {
            $permission = intval($permission);
        } else {
            $permission = $permission->getKey();
        }

        $this->permissions()->attach($permission);
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current role.
     *
     * @param object|array $permission
     * @return void
     */
    public function detachPermission($permission)
    {
        $permission = $permission->getKey();

        $this->permissions()->detach($permission);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param mixed $permissions
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param mixed $permissions
     * @return void
     */
    public function detachPermissions($permissions = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }

        return $this;
    }
}

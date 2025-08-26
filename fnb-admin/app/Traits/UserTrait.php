<?php

namespace App\Traits;

use App\Models\User;

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Traits\RoleTrait;

trait UserTrait
{

    use RoleTrait;

    protected function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'roles_for_user_'.$this->$userPrimaryKey;
        if (Cache::getStore() instanceof \Illuminate\Cache\TaggableStore) {
            return Cache::tags(Config::get('tbl_role_user'))->remember($cacheKey,
                Config::get('cache.ttl', 60), function () {
                    return $this->roles()->get();
                });
        } else {
            return $this->roles()->get();
        }
    }

    public function cachedPermissionsUser()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'permissions_for_user_'.$this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags('user_permission')->remember($cacheKey, Config::get('cache.ttl', 60),
                function () {
                    return $this->permissionsUser()->get();
                });
        } else {
            return $this->permissionsUser()->get();
        }

    }

    public function permissionsUser()
    {
        return $this->belongstoMany('App\Models\Permission', 'tbl_user_permission', 'user_id', 'permission_id');
    }

    /**
     * Flush the role's cache.
     *
     * @return void
     */
    public function flushCache()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags('user_permission')->flush();
        }
        return $this;
    }

    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        $flushCache = function ($user) {
            $user->flushCache();
        };
        // If the user doesn't use SoftDeletes.
        if (method_exists(static::class, 'restored')) {
            static::restored($flushCache);
        }
        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($user) {
            $user->roles()->sync([]);
        });
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        return $this->belongstoMany('App\Models\Role', 'tbl_role_user', 'user_id', 'role_id');
    }

    /**
     * Get the the names of the user's roles.
     *
     * @return bool
     */
    public function getRoles()
    {
        return null;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->display_name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     * @return bool
     */
    public function hasPermission($permission_parent = '', $permission, $requireAll = false)
    {
        if (empty($permission_parent)) {
            return false;
        }
        $permission = $this->standardize($permission);
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->hasPermission($permission_parent, $permName);
                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                foreach ($role->cachedPermissions() as $perm) {
                    if (Str::is($permission, $perm->display_name) && $perm->groupPermission->display_name == $permission_parent) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function hasPermissionUser($permission_parent = '', $permission, $requireAll = false)
    {
        if (empty($permission_parent)) {
            return false;
        }
        $permission = $this->standardize($permission);
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->hasPermission($permission_parent, $permName);

                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            foreach ($this->cachedPermissionsUser() as $perm) {
                if (Str::is($permission, $perm->display_name) && $perm->groupPermission->display_name == $permission_parent) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function _can($permisson_parent, $permission, $requireAll = false)
    {

        return $this->hasPermission($permisson_parent, $permission, $requireAll);
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     * @return bool
     */
    public function isAbleTo($permisson_parent, $permission, $requireAll = false)
    {
        return $this->hasPermission($permisson_parent, $permission, $requireAll);
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array $options validate_all (true|false) or return_type (boolean|array|both)
     *
     * @return array|bool
     * @throws \InvalidArgumentException
     *
     */
    public function ability($roles, $permissions, $options = [])
    {
        // Convert string to array if that's what is passed in.
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if (!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }

        if (!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }

        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @return static
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    /**
     * Attach multiple roles to a user.
     *
     * @param mixed $roles
     * @return static
     */
    public function attachRoles($roles = [])
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }

        return $this;
    }

    /**
     * Detach multiple roles from a user.
     *
     * @param mixed $roles
     * @return static
     */
    public function detachRoles($roles = [])
    {
        if (empty($roles)) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }

        return $this;
    }

    /**
     * Sync All roles to a user.
     *
     * @param mixed $roles
     * @return static
     */
    public function syncRoles()
    {
        $this->flushCache();

        return $this;
    }

    /**
     * Return all the user permissions.
     *
     * @return \Illuminate\Support\Collection|static
     */
    public function allPermissions()
    {
        $roles = $this->roles()->with('permissions')->get();

        $roles = $roles->flatMap(function ($role) {
            return $role->permissions()->with('groupPermission')->get();
        });

        return $roles;
    }

    public function allPermissionsUser()
    {

        return $this->permissionsUser()->with('groupPermission')->get();
    }

    /**
     *Filtering users according to their role
     *
     * @param string $role
     * @return users collection
     */
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($query) use ($role) {
            $query->where('display_name', $role);
        });
    }

    /**
     * Checks if the string passed contains a pipe '|' and explodes the string to an array.
     * @param string|array $value
     * @return string|array
     */
    public function standardize($value, $toArray = false)
    {
        if (is_array($value) || ((strpos($value, '|') === false) && !$toArray)) {
            return $value;
        }

        return explode('|', $value);
    }
}

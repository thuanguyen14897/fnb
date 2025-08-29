<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait;
    protected $table='tbl_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    function department()
    {
        return $this->belongstoMany('App\Models\Department', 'tbl_user_department', 'user_id', 'department_id');
    }

    function role()
    {
        return $this->belongstoMany('App\Models\Role', 'tbl_role_user', 'user_id', 'role_id');
    }

    function permission()
    {
        return $this->belongstoMany('App\Models\Permission', 'tbl_user_permission', 'user_id', 'permission_id');
    }

    function user_ares()
    {
        return $this->hasMany(\App\Models\UserAres::class, 'id_user', 'id');
    }
}

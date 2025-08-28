<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserAres extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait;
    protected $table='tbl_user_ares';

//    function ares()
//    {
//        return $this->belongstoMany('App\Models\Department', 'tbl_user_department', 'user_id', 'department_id');
//    }


}

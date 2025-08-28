<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Clients extends Model
{
    use HasFactory;
    protected $table='tbl_clients';
    protected $fillable = [
        'fullname',
        'avatar',
        'phone',
        'email',
        'type_client',
        'prefix_phone',
        'password',
        'sign_up_with',
        'id_sign_up',
        'active',
        'gender',
        'birthday',
        'created_at',
        'number_cccd',
        'issued_cccd',
        'date_cccd',
        'number_passport',
        'issued_passport',
        'date_passport',
    ];


    function province()
    {
        return $this->belongstoMany('App\Models\Province', 'tbl_client_address', 'customer_id', 'province_id','id','Id');
    }

    function ward()
    {
        return $this->belongstoMany('App\Models\Ward', 'tbl_client_address', 'customer_id', 'ward_id','id','Id');
    }

    function address()
    {
        return $this->hasMany('App\Models\ClientAddress', 'customer_id', 'id');
    }

    function representative()
    {
        return $this->hasOne('App\Models\PartnerRepresentative', 'customer_id', 'id');
    }

    function image_cccd()
    {
        return $this->hasMany('App\Models\PartnerImage', 'customer_id', 'id')->where('type','=',1);
    }

    function image_kd()
    {
        return $this->hasMany('App\Models\PartnerImage', 'customer_id', 'id')->where('type','=',2);
    }
}

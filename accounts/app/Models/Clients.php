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
        'membership_level',
        'point_membership',
        'ranking_date',
        'active_limit_private',
        'invoice_limit_private',
        'radio_discount_private',
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

    function customer_package()
    {
        return $this->hasOne('App\Models\CustomerPackage', 'customer_id', 'id');
    }

    function transaction_package()
    {
        return $this->hasOne('App\Models\TransactionPackage', 'customer_id', 'id')->where('status','=',1);
    }

    function referral_level()
    {
        return $this->hasOne('App\Models\ReferralLevel', 'customer_id', 'id');
    }

    function referral_level_child()
    {
        return $this->hasMany('App\Models\ReferralLevel', 'parent_id', 'id');
    }

    function history_membership_level()
    {
        return $this->belongsTo('App\Models\HistoryCustomerMemberShipLevel', 'customer_id', 'id');
    }
}

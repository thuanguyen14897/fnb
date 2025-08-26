<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    use HasFactory;
    protected $table='tbl_client_address';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'Id');
    }

    function district()
    {
        return $this->belongsTo('App\Models\District', 'district_id', 'district_id');
    }

    function ward()
    {
        return $this->belongsTo('App\Models\Ward',  'ward_id', 'Id');
    }
}

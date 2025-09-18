<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPackage extends Model
{
    use HasFactory;
    protected $table = 'tbl_customer_package';

    function customer()
    {
        return $this->hasOne('App\Models\Clients', 'id', 'customer_id');
    }

    function package()
    {
        return $this->belongsTo('App\Models\Package', 'package_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPackage extends Model
{
    use HasFactory;
    protected $table = 'tbl_transaction_package';

    function package()
    {
        return $this->belongsTo('App\Models\Package', 'package_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function customer_package()
    {
        return $this->hasOne('App\Models\CustomerPackage', 'transaction_package_id', 'id');
    }
}

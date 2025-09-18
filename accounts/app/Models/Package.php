<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'tbl_package';

    function transaction_package()
    {
        return $this->hasMany('App\Models\TransactionPackage', 'package_id', 'id');
    }

}

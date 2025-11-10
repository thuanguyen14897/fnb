<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryCustomerMemberShipLevel extends Model
{
    use HasFactory;
    protected $table='tbl_history_customer_membership_level';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}
